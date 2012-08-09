#!/usr/bin/python

import fileinput
import urllib2

class Page:
    """
    A crawlable page/path
    """
    def get_path(self):
        pass

    def get_request_type(self):
        return "GET"

    def get_params(self):
        pass

class CrawlablePages:
    """
    An interface that provides a list of paths that can be crawled.
    """
    def has_next(self):
        pass

    def next(self):
        pass

    def current(self):
        pass
    
class SimplePage(Page):

    def __init__(self,path):
        self._path = path

    def get_path(self):
        return self._path

    def get_request_type(self):
        return "GET"

    def get_params(self):
        return dict()

class SimplePagesSource(Page):

    def __init__(self, file):
        self._paths = []
        self._pos = 0
        for line in fileinput.input(file):
            self._paths.append(line.strip())

    def has_next(self):
        return len(self._paths)-1 >= self._pos

    def next(self):
        p = self._pos
        self._pos += 1
        return SimplePage(self._paths[p])

    def current(self):
        return SimplePage(self._paths[self._pos - 1])

