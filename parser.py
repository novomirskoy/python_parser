from bs4 import BeautifulSoup
from urllib2 import urlopen

html_doc = urlopen('http://nnov.am.ru/all/search/').read()
soup = BeautifulSoup(html_doc)

count_pages = soup.find("div", class_="paginator-amount").get_text()
count = count_pages.split()
pages = count[3]

