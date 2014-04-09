# -*- coding: utf-8 -*-

import pymongo
import json
import time
import locale
import re
import base64
from bs4 import BeautifulSoup
from urllib2 import urlopen
from PIL import Image

locale.setlocale(locale.LC_ALL, "")

client = pymongo.MongoClient("localhost", 27017)
db = client.adverts

url = "http://nnov.am.ru/all/search/"
url_page = "http://nnov.am.ru/all/search/?p="


def get_count_pages(url):
    """Возвращает количество страниц"""

    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)

    count_pages = soup.find("div", class_="paginator-amount").get_text()
    count = count_pages.split()
    pages = count[3]

    return pages


def parse_page(url_page):
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
    digit_string = re.compile('[\d]+.?[\d]*')
    found = re.findall(digit_string, string)

    return found[0]


def parse_advert_page(advert):
    url = advert.get("advert_url")
    id = advert.get("_id")
    print "URL: ", url
    print "ID: ", id
    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)

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
            print phone_number
            ff = open("number.txt", "a+")
            ff.write("Декодирован -- ")
            ff.write(url + " -- ")
            ff.write(phone_number + "\n")
            ff.close()
    else:
        img = urlopen(phone).read()
        img_file = open("phone/"+str(id)+".png", "wb")
        img_file.write(img)
        img_file.close()
        phone_number = image_to_string(Image.open("phone/"+str(id)+".png"))
        print phone_number
        ff = open("number.txt", "a+")
        ff.write(url + " -- ")
        ff.write(phone_number + "\n")
        ff.close()
    # advert = {}
    #
    # brandTag = soup.find(attrs={"data-object": "brand"})
    # brand = brandTag.span.string
    # advert["brand"] = brand
    #
    # modelTag = soup.find(attrs={"data-object": "model"})
    # model = modelTag.span.string
    # advert["model"] = model
    #
    # yearTag = soup.find(attrs={"data-object": "year"})
    # year = yearTag.string
    # advert["year"] = year[1:]
    #
    # images = []
    # images_ul = soup.find_all("li", class_="b-rama-thumbs__item")
    #
    # if bool(images_ul) is not False:
    #     for li in images_ul:
    #         try:
    #             image = li.a["data-original"]
    #         except KeyError:
    #             continue
    #         else:
    #             images.append(image)
    # advert["images"] = images
    #
    # try:
    #     custom_house_state = soup.find("span", class_="b-card-status__text").string
    # except AttributeError:
    #     advert["custom_house_state"] = ""
    # else:
    #     custom_house_state = custom_house_state.split(",")[0]
    #     advert["custom_house_state"] = custom_house_state
    #
    # params_table = {}
    # tables = soup.find_all("table", class_="b-card-info__table")
    # for table in tables:
    #     for tr in table:
    #         row = tr.find_all("td")
    #         try:
    #             key = row[0].get_text()
    #             key = key[:-1]
    #             value = row[1].get_text()
    #         except IndexError:
    #             span = row[0].find_all("span")
    #             key = span[0].get_text()
    #             key = key[:-1]
    #             value = span[1].get_text()
    #             params_table[key] = value
    #         else:
    #             params_table[key] = value
    #
    # for key in params_table.keys():
    #     try:
    #         new_key = params.get(key)
    #     except KeyError:
    #         continue
    #     else:
    #         advert[new_key] = params_table[key]
    #
    # engine = advert.get("engine_type")
    # engine = engine.split("/")
    # len_engine = len(engine)
    # if len_engine == 3:
    #     advert["engine_volume"] = filter_string(engine[0].strip())
    #     advert["engine_power"] = filter_string(engine[1].strip())
    #     advert["engine_type"] = engine[2].strip()
    # elif len_engine == 2:
    #     try:
    #         advert["engine_volume"] = filter_string(engine[0].strip())
    #         advert["engine_type"] = engine[1].strip()
    #     except IndexError:
    #         print engine[0].strip()
    #         print engine[1].strip()
    #         time.sleep(20)
    # else:
    #     advert["engine_volume"] = filter_string(engine[0].strip())
    #
    # for key in advert.keys():
    #     print "%s => %s" % (key, advert[key])

    # db.am.update({"advert_url": url}, {"$set": {"advert": json.dumps(advert)}})

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
}


# db.am.remove()
# pages = get_count_pages(url)
# print pages
# parse_pages(pages)


count_adverts = db.am.find().count()
print "Количество объявлений: ", count_adverts

counter = 1
for advert in db.am.find():
    print "Номер объявления: ", counter
    parse_advert_page(advert)
    counter += 1


# for advert in db.am.find():
#     advert = json.loads(advert.get("advert"))
#     print advert.get("engine_power")
