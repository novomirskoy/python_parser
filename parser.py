# -*- coding: utf-8 -*-

import pymongo
from bs4 import BeautifulSoup
from urllib2 import urlopen

client = pymongo.MongoClient("localhost", 27017)
db = client.parser

url = "http://nnov.am.ru/all/search/"
url_page = "http://nnov.am.ru/all/search/?p="


def get_count_pages(url):
    pages = db.pages.find_one()
    if pages is None:
        html_doc = urlopen(url).read()
        soup = BeautifulSoup(html_doc)

        count_pages = soup.find("div", class_="paginator-amount").get_text()
        count = count_pages.split()
        pages = count[3]

        db.pages.remove()
        db.pages.save({"count": pages})

        return pages
    else:
        return pages["count"]


def parse_page(url_page):
    html_doc = urlopen(url_page).read()
    soup = BeautifulSoup(html_doc)

    adverts = soup.find_all("div", class_="b-snippet ungrouped unmarked editable")
    for advert in adverts:
        db.am_ru.save({"advert": advert})

pages = get_count_pages(url)
print pages

#advert = urlopen('').read()
#advert_soup = BeautifulSoup(advert)
# for i in xrange(0, int(pages)):
#     print url_page + str(i)

parse_page(url_page+'1')