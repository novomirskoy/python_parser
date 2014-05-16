# -*- coding: utf-8 -*-

import pymongo
import json
import locale
import re
import base64
import os
import sys
import shutil
import hashlib
import urllib2
import pyocr
import pyocr.builders
from urlparse import urlparse
from urlparse import urlsplit, urlunsplit
from bs4 import BeautifulSoup
from urllib2 import urlopen
from PIL import Image

locale.setlocale(locale.LC_ALL, "")

client = pymongo.MongoClient("localhost", 27017)
db = client.adverts

url = "http://nnov.am.ru/all/search/"
url_page = "http://nnov.am.ru/all/search/?p="

params = {
    u"Состояние": "state",
    u"Двигатель": "engine_type",
    u"КПП": "kpp",
    u"Кузов": "body",
    u"Цвет": "color",
    u"Привод": "transmission",
    u"Руль": "steering_wheel",
    u"Владельцев по ПТС": "owners",
    u"VIN-код": "vin",
    u"Регион": "region",
    u"Пробег": "distance",
}


# Копирование дерева каталогов
def copytree(src, dst, symlinks=0):
    print "Копирование папки " + src
    names = os.listdir(src)
    if not os.path.exists(dst):
        os.mkdir(dst)
        os.chmod(dst, 0775)
    for name in names:
        src_name = os.path.join(src, name)
        dst_name = os.path.join(dst, name)
        try:
            if symlinks and os.path.islink(src_name):
                link_to = os.readlink(src_name)
                os.symlink(link_to, dst_name)
            elif os.path.isdir(src_name):
                copytree(src_name, dst_name, symlinks)
            else:
                shutil.copy(src_name, dst_name)
        except (IOError, os.error) as why:
            print "Невозможно скопировать %s в %s: %s" % (src_name, dst_name, str(why))


# Возвращает количество страниц
def get_count_pages(url):
    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)

    count_pages = soup.find("div", class_="paginator-amount").get_text()
    count = count_pages.split()
    pages = count[3]

    return pages


# Парсит страницу со списком объявлений и сохраняет их url
def parse_page(url_page):
    html_doc = urlopen(url_page).read()
    soup = BeautifulSoup(html_doc)

    adverts = soup.find_all("div", class_="title")
    for advert in adverts:
        advert_url = advert.a.get("href")
        print advert.a.get("href")
        db.am_ru_adverts.insert({"advert_url": advert_url, "advert": ""})


def parse_pages(count_pages):
    for i in xrange(0, int(count_pages)):
        print "Номер страницы:", i
        page = url_page + str(i)
        print page
        parse_page(page)


def filter_string(string):
    """Фильтрует строки осталвяя только цифры"""

    digit_string = re.compile('[\d]+.?[\d]*')
    found = re.findall(digit_string, string)

    return found[0]


# Распознаёт изображение с номером телефона
def recognition_phone(phone_img_file):
    tools = pyocr.get_available_tools()
    tool = tools[0]
    langs = tool.get_available_languages()
    lang = langs[0]
    phone_text = tool.image_to_string(Image.open(phone_img_file), lang=lang, builder=pyocr.builders.TextBuilder())

    return phone_text


# Скачивает изображение и обрезает на 50 пикселей снизу
def download_and_crop(image_url, id):
    img = urlopen(image_url).read()
    img_dir = "images/"

    img_file_name = str(id)+"/"+image_url[-36:]
    img_file = open(img_dir + img_file_name, "wb")
    img_file.write(img)
    img_file.flush()
    img_file.close()

    img_original = Image.open(img_dir + img_file_name)
    width, height = img_original.size
    img_crop = img_original.crop([0, 0, width, height - 50])
    img_crop.save(img_dir + img_file_name, quality=100)

    return img_file_name


def parse_advert_page(advert):
    url = advert.get("advert_url")
    id = advert.get("_id")

    print "========================"
    print "URL: ", url
    print "ID: ", id

    try:
        html_doc = urlopen(url).read()
    except urllib2.HTTPError:
        return False
    else:
        soup = BeautifulSoup(html_doc)

    advert = {}

    # Продавец и Регион и Место осмотра
    contact_info = soup.find_all("dl", class_="clearfix")

    try:
        seller = contact_info[0].dd.get_text()
    except IndexError:
        advert["seller"] = ""
    else:
        advert["seller"] = seller

    try:
        advert["seller"] = re.sub("^\s+|\n|\r|\s+$", '', advert["seller"])
    except TypeError:
        pass
    else:
        if u'Задать вопрос' in advert["seller"]:
            advert["seller"] = advert["seller"][:-14]

    # не будем парсить объявления с незаполненым полем "Имя владельца"
    if len(advert["seller"]) == 0:
        return False

    try:
        contact_info[1].dt.get_text()
    except IndexError:
        pass
    else:
        if (contact_info[1].dt.get_text()) == u'Адрес:':
            try:
                address = contact_info[1].dd.get_text()
            except IndexError:
                address = ""
            else:
                advert["region"] = address.split(",")[0]
                inspection_place = address.strip()[:-10]
                advert["inspection_place"] = inspection_place.strip()
        else:
            try:
                region = contact_info[1].dd.get_text()
            except IndexError:
                advert["region"] = ""
            else:
                advert["region"] = region

            if (contact_info[2].dt.get_text()) == u'Осмотр:':
                try:
                    inspection_place = contact_info[2].dd.get_text()
                except IndexError:
                    advert["inspection_place"] = ""
                else:
                    inspection_place = inspection_place.strip()[:-10]
                    advert["inspection_place"] = inspection_place.strip()

    # Изображения
    images = []
    images_ul = soup.find_all("li", class_="b-rama-thumbs__item")

    if bool(images_ul) is not False:
        if not os.path.exists("images/"+str(id)):
            os.mkdir("images/"+str(id))

        for li in images_ul:
            try:
                image_path = li.a["data-original"]
            except KeyError:
                continue
            else:
                image_path = urlparse(image_path)

                path = image_path.path
                path = path.split("/")
                path[3] = "default"
                path = "/".join(path)

                scheme = image_path.scheme
                netloc = image_path.netloc
                query = image_path.query
                fragment = image_path.fragment

                new_path = (scheme, netloc, path, query, fragment)
                image_path = urlunsplit(new_path)
                print image_path

                url_image = urlparse(image_path)
                url_path = url_image.path
                pattern = re.compile('^\/autocatalog')
                find = re.findall(pattern, url_path)

                if bool(find):
                    return False
                else:
                    image_file = download_and_crop(image_path, id)
                    images.append(image_file)
    else:
        return False

    advert["images"] = images

    try:
        brand_tag = soup.find(attrs={"data-object": "brand"})
        brand = brand_tag.span.string
    except AttributeError:
        pass
    else:
        # Марка
        brand_tag = soup.find(attrs={"data-object": "brand"})
        brand = brand_tag.span.string
        advert["brand"] = brand

        # Модель
        model_tag = soup.find(attrs={"data-object": "model"})
        model = model_tag.span.string
        advert["model"] = model

        # Год
        year_tag = soup.find(attrs={"data-object": "year"})
        year = year_tag.string
        advert["year"] = year[1:]

        # Цена
        price_tag = soup.find("span", class_="b-card-price__price")
        price = price_tag.get_text()
        advert["price"] = price

        # Телефон
        phone_tag = soup.find("img", class_="phone-part")
        try:
            phone = phone_tag.get("src")
        except AttributeError:
            try:
                phone_tag = soup.find("b", class_="phone-part")
                phone_encoded = phone_tag.get("data-encoded-phone")
            except AttributeError:
                pass
            else:
                phone_number = base64.b64decode(phone_encoded + "=")
                advert["phone"] = phone_number
        else:
            try:
                img = urlopen(phone).read()
            except urllib2.HTTPError:
                return False
            else:
                img_file = open("phone/"+str(id)+".png", "wb")
                img_file.write(img)
                img_file.close()
                phone_number = recognition_phone("phone/"+str(id)+".png")
                advert["phone"] = phone_number

        # Текст объявления
        text_tag = soup.find("div", class_="au-block au-block-0")
        try:
            text = text_tag.p.string
        except AttributeError:
            advert["text"] = ""
        else:
            advert["text"] = text

        # Таможка
        try:
            custom_house_state = soup.find("span", class_="b-card-status__text").string
        except AttributeError:
            advert["custom_house_state"] = ""
        else:
            custom_house_state = custom_house_state.split(",")[0]
            advert["custom_house_state"] = custom_house_state

        params_table = {}
        tables = soup.find_all("table", class_="b-card-info__table")
        for table in tables:
            for tr in table:
                row = tr.find_all("td")
                try:
                    key = row[0].get_text()
                    key = key[:-1]
                    value = row[1].get_text()
                except IndexError:
                    span = row[0].find_all("span")
                    key = span[0].get_text()
                    key = key[:-1]
                    value = span[1].get_text()
                    params_table[key] = value
                else:
                    params_table[key] = value

        for key in params_table.keys():
            try:
                new_key = params.get(key)
            except KeyError:
                continue
            else:
                advert[new_key] = params_table[key]

        # КПП
        kpp = advert.get("kpp")
        advert["kpp"] = kpp.split(",")[0]

        # Двигатель
        engine = advert.get("engine_type")
        engine = engine.split("/")
        len_engine = len(engine)
        if len_engine == 3:
            advert["engine_volume"] = filter_string(engine[0].strip())
            advert["engine_power"] = filter_string(engine[1].strip())
            advert["engine_type"] = engine[2].strip()
        elif len_engine == 2:
            try:
                advert["engine_volume"] = filter_string(engine[0].strip())
                advert["engine_type"] = engine[1].strip()
                advert["engine_power"] = ""
            except IndexError:
                print engine[0].strip()
                print engine[1].strip()
        else:
            advert["engine_volume"] = filter_string(engine[0].strip())
            advert["engine_type"] = ""
            advert["engine_power"] = ""

    # for key in advert.keys():
    #     print "%s => %s" % (key, advert[key])

    hash_md5 = hashlib.md5(json.dumps(advert, sort_keys=True)).hexdigest()
    db.am_ru_adverts.update({"advert_url": url}, {"$set": {"advert": json.dumps(advert), "hash": hash_md5}})
    print "Сохранено"


# удаление изображений
def remove_all_images():
    if os.path.exists("images/"):
        shutil.rmtree("images/")


def main():
    action_type = None
    print "Список действий:"
    print "1 - удалить все имеющиеся объявления и изображения"
    print "2 - поиск всех объялений на сайте"
    print "3 - парсинг всех объявлений"
    print "4 - перенос изображений"
    print "5 - выход из программы"

    while action_type != 5:
        action_type = int((raw_input("[]-> ")))

        # действие 1
        if action_type == 1:
            db.am_ru_adverts.remove()
            remove_all_images()

        # действие 2
        elif action_type == 2:
            number_of_pages = get_count_pages(url)
            print number_of_pages

            parse_pages(number_of_pages)
            count_adverts = db.am_ru_adverts.find().count()
            print "Количество объявлений: ", count_adverts

        # действие 3
        elif action_type == 3:
            counter = 1

            if not os.path.exists("images"):
                os.mkdir("images")

            for advert in db.am_ru_adverts.find():
                print "Номер объявления: ", counter
                parse_advert_page(advert)

                counter += 1

        # действие 4
        elif action_type == 4:
            for file_path, dirs, files in os.walk("images"):
                for dir_name in dirs:
                    os.chmod(os.path.join(file_path, dir_name), 0775)
                for file_name in files:
                    os.chmod(os.path.join(file_path, file_name), 0775)

            path_to_copy = "tmp"
            copytree("images", path_to_copy)


if __name__ == "__main__":
    main()
