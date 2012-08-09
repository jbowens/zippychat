#!/usr/bin/python

from optparse import OptionParser
from crawler import SimplePagesSource
from logwatcher import LogWatcher
import urllib2
import sys

"""
crawl-server.py

@author jbowens

This script is useful for crawling the site with the hope of producing any existing errors.
Before any changes are merged into the production branch, they should be crawled through
this script, and it should be verified that no errors were produced.
"""

parser = OptionParser()
parser.add_option("-s", "--server", dest="server", help="The server/host to crawl", default="localhost")
parser.add_option("-p", "--port", dest="port", help="The port on the server to make requests", default=80)
parser.add_option("-f", "--files", dest="files", help="Comma-separated list of files of paths to crawl", 
        default="crawlable-pages-static.txt")
parser.add_option("-v", "--verbose", dest="verbose", action="store_true", help="Print verbose output", default=False)

(options, args) = parser.parse_args()

if not options.server:
    print "You must provide a server."
    sys.exit(1)

print "Going to crawl %s" % options.server

crawlable_page_sources = []

def url(url):
    return "http://" + options.server + ":" + str(options.port) + url

#def process_log_output(filename, output):
#    print output

if options.files and len(options.files) > 1:
    for filename in options.files.split(","):
        if options.verbose:
            print "Loading pages from %s" % filename
        crawlable_page_sources.append(SimplePagesSource(filename))

#logwatcher = LogWatcher("/opt/local/apache2/htdocs/logs/", process_log_output)
#logwatcher.loop(async=True)

print "Beginning crawl"
print "-" * 80

successes = 0
failures = 0

for source in crawlable_page_sources:
    while source.has_next():
        page = source.next()

        absolute_url = url(page.get_path())
        if options.verbose:
            print "Hitting page %s" % absolute_url
        req = urllib2.Request(absolute_url)
        try:
            response = urllib2.urlopen(req)
            meta_info = response.info()
        except HTTPError, e:
            print "Received error code %d on request to %s" % (e.code,page.get_path())
            failures += 1
            pass
        except URLError, e:
            print "URLError: %s" % e.reason
            failures += 1
            pass
        else:
            successes += 1

print "-" * 80

if failures == 0:
    print "CRAWL SUCCESS"
else:
    print "CRAWL FAILURE"

print "%u successful requests and %u failures" % (successes,failures)
