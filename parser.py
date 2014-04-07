# -*- coding: utf-8 -*-

import pymongo
import json
import time
import locale
from bs4 import BeautifulSoup
from urllib2 import urlopen
from bson.objectid import ObjectId
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


def parse_advert_page(url_advert):
    for advert in adverts:
        print advert["advert_url"]

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
# db.pages.remove()
# db.am.remove()
# pages = get_count_pages(url)
# print pages
# parse_pages(pages)
count_adverts = db.am.find().count()
print "Количество объявлений: ", count_adverts

for advert in db.am.find():
    url = advert.get("advert_url")
    id = advert.get("_id")
    # print url
    html_doc = urlopen(url).read()
    soup = BeautifulSoup(html_doc)
    advert = {}

    brandTag = soup.find(attrs={"data-object": "brand"})
    brand = brandTag.span.string
    advert["brand"] = brand

    modelTag = soup.find(attrs={"data-object": "model"})
    model = modelTag.span.string
    advert["model"] = model

    yearTag = soup.find(attrs={"data-object": "year"})
    year = yearTag.string
    advert["year"] = year[1:]

    images = []
    images_ul = soup.find_all("li", class_="b-rama-thumbs__item")

    if bool(images_ul) is not False:
        for li in images_ul:
            try:
                image = li.a["data-original"]
            except KeyError:
                continue
            else:
                images.append(image)
    advert["images"] = images

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

    engine = advert.get("engine_type")
    engine = engine.split("/")
    try:
        advert["engine_type"] = engine[2].strip()
    except IndexError:
        advert["engine_power"] = ""
    else:
        advert["engine_volume"] = engine[0].strip()
        advert["engine_type"] = engine[1].strip()

    for key in advert.keys():
        print "%s => %s" % (key, advert[key])

    db.am.update({"_id": id}, {"$set": {"advert": json.dumps(advert)}})




# ii = 0
# for image in images:
#     # fileimage = urlopen(image).read()
#     # f = open(str(ii)+".jpg", "wb")
#     # f.write(fileimage)
#     # f.close
#     # print image
#     img = Image.open(str(ii)+".jpg")
#     width, height = img.size
#     left = 0
#     top = 0
#     right = width
#     bottom = int(height) - 50
#     img2 = img.crop((left, top, right, bottom))
#     img2.save(str(ii)+"-crop.jpg", "JPEG", quality=100)
#     ii += 1
