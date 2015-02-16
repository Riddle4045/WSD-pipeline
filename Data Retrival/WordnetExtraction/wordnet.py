#!/usr/bin/env python
 
from nltk.corpus import wordnet as wn
from nltk.stem import PorterStemmer

stemmer = PorterStemmer()
count = 0

f =open('myfile.txt','w')
for i in wn.all_synsets():
    if i.pos() in ['n']: # If synset is adj or satelite-adj.
		name = i.name()
		count = count + 1
		print stemmer.stem(name)
		f.write(stemmer.stem(name)+"\n") # python will convert \n to os.linesep

print "total words" 
print count


f.close() 
