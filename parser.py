# -*- coding: utf-8 -*-

import pymongo
import json
import locale
import re
import base64
import os
import pyocr
import pyocr.builders
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


def get_count_pages(url):
    """Возвращает количество страниц"""

    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)

    count_pages = soup.find("div", class_="paginator-amount").get_text()
    count = count_pages.split()
    pages = count[3]

    return pages


def parse_page(url_page):
    """
    Парсит страницу со списком объявлений
    и сохраняет их url
    """

    html_doc = urlopen(url_page).read()
    soup = BeautifulSoup(html_doc)

    adverts = soup.find_all("div", class_="title")
    for advert in adverts:
        advert_url = advert.a.get("href")
        print advert.a.get("href")
        db.am.insert({"advert_url": advert_url})


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


def recognition_phone(phone_img_file):
    """Распознаёт изображение с номером телефона"""

    tools = pyocr.get_available_tools()
    tool = tools[0]
    langs = tool.get_available_languages()
    lang = langs[0]
    phone_text = tool.image_to_string(Image.open(phone_img_file), lang=lang, builder=pyocr.builders.TextBuilder())

    return phone_text


def download_and_crop(image_url, id):
    """Скачивает изображение и обрезает по указанному размеру"""

    img = urlopen(image_url).read()
    img_file_name = "images/"+str(id)+"/"+image_url[-36:]
    img_file = open(img_file_name, "wb")
    img_file.write(img)
    img_file.flush()
    img_file.close()
    img_original = Image.open(img_file_name)
    width, height = img_original.size
    img_crop = img_original.crop([0, 0, width, height - 50])
    img_crop.save(img_file_name)

    return img_file_name


def parse_advert_page(advert):
    url = advert.get("advert_url")
    id = advert.get("_id")

    print "URL: ", url
    print "ID: ", id

    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)

    advert = {}

    try:
        brandTag = soup.find(attrs={"data-object": "brand"})
        brand = brandTag.span.string
    except AttributeError:
        pass
    else:
        """Марка"""
        brandTag = soup.find(attrs={"data-object": "brand"})
        brand = brandTag.span.string
        advert["brand"] = brand

        """Модель"""
        modelTag = soup.find(attrs={"data-object": "model"})
        model = modelTag.span.string
        advert["model"] = model

        """Год"""
        yearTag = soup.find(attrs={"data-object": "year"})
        year = yearTag.string
        advert["year"] = year[1:]

        """Цена"""
        priceTag = soup.find("span", class_="b-card-price__price")
        price = priceTag.get_text()
        advert["price"] = price

        """Продавец и Регион и Место осмотра"""
        contact_info = soup.find_all("dl", class_="clearfix")

        try:
            seller = contact_info[0].dd.get_text("")
        except IndexError:
            advert["seller"] = ""
        else:
            advert["seller"] = seller[:-14]

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

        """Телефон"""
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
            img = urlopen(phone).read()
            img_file = open("phone/"+str(id)+".png", "wb")
            img_file.write(img)
            img_file.close()
            phone_number = recognition_phone("phone/"+str(id)+".png")
            advert["phone"] = phone_number

        """Текст объявления"""
        textTag = soup.find("div", class_="au-block au-block-0")
        try:
            text = textTag.p.string
        except AttributeError:
            advert["text"] = ""
        else:
            advert["text"] = text

        """Изображения"""
        images = []
        images_ul = soup.find_all("li", class_="b-rama-thumbs__item")

        if bool(images_ul) is not False:
            if not os.path.exists("images/"+str(id)):
                os.mkdir("images/"+str(id))

            for li in images_ul:
                try:
                    image = li.a["data-original"]
                except KeyError:
                    continue
                else:
                    image_file = download_and_crop(image, id)
                    images.append(image_file)

        advert["images"] = images

        """Таможка"""
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

        """КПП"""
        kpp = advert.get("kpp")
        advert["kpp"] = kpp.split(",")[0]

        """Двигатель"""
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

    for key in advert.keys():
        print "%s => %s" % (key, advert[key])

    db.am.update({"advert_url": url}, {"$set": {"advert": json.dumps(advert)}})


def main():
    action_type = None
    print "Список действий:"
    print "1 - удалить все имеющиеся объявления"
    print "2 - парсинг всех объявлений"
    print "3 - удаление объявлений не соответсвующих требованиям"
    print "4 - поиск изменений"
    print "5 - выход из программы"

    while action_type != 5:
        action_type = int((raw_input("[]-> ")))

        if action_type == 1:
            db.am.remove()
        elif action_type == 2:
            number_of_pages = get_count_pages(url)
            print number_of_pages
            parse_pages(number_of_pages)
            count_adverts = db.am.find().count()
            print "Количество объявлений: ", count_adverts
            counter = 1
            for advert in db.am.find():
                print "Номер объявления: ", counter
                parse_advert_page(advert)
                counter += 1
        elif action_type == 3:
            pass
        elif action_type == 4:
            pass


    # for advert in db.am.find():
    #     advert = json.loads(advert.get("advert"))
    #     print advert.get("engine_power")

if __name__ == "__main__":
    main()
