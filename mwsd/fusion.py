import os
import sys


def readResults(filename):
    ts = []
    ls = []
    with open(filename,"r+") as f:
        for lines in f:
            line = lines.split();
            

it, ip, il = readResults(sys.argv[1])

