#!/usr/bin/env python
import urllib2 
import urllib
import sys
from nltk.corpus import wordnet as wn
from nltk.stem import PorterStemmer



urllib.urlretrieve("http://www.image-net.org/api/text/imagenet.synset.obtain_synset_list",filename="synset_list.txt");
synsetlist = []
with open("synset_list.txt") as synset_list:
			for line in synset_list:
				
				key = str(line).rstrip('\n');
				#print key; 
				synsetlist.append(key);


#print synsetlist;		
#ss = wn._synset_from_pos_and_offset('n',2100735);

query_word = sys.argv[1];
query_synsets = wn.synsets(query_word);

print "Fectching Synsets and Definitionsfrom WordNet.."
wordNet_Synset = {};
for synset in query_synsets:
		if synset.pos() in ['n']:
				offset = str(synset.pos()) + str( synset.offset());
				wordNet_Synset[offset] = str(synset.name());

print wordNet_Synset

#final result to return this will contain  the synset offfset -- wordNet List  -- ImagenetList 
#imagenet list and wordNet list will be intersected

result = {};
print "Creating a Mapping from WordNet Synsets to imageNet Synsets.."
matching_wnids = [];
base_url = "http://www.image-net.org/api/text/wordnet.synset.getwords?wnid=";


#open the imageNet Offset words mapping
query_offsets = {}; 
for key,elem in wordNet_Synset.items():
			 query_offsets[key] = elem;

#print synsetlist;
for wnid,elem in query_offsets.items():
		if  wnid  in synsetlist:
			matching_wnids.append(wnid);
		else:
		    print  wnid +   " not in imagenet"
	
#generate the appropraite URL 
imageNet_Syn_names = {};
for key in matching_wnids:
			url = base_url + key
			for line in urllib2.urlopen(url):
					imageNet_Syn_names[key] = line


result[sys.argv[1]] = {"wordnet" : query_offsets , "imageNet" : imageNet_Syn_names};

print result;

#get the data from ImageNet API

#generate the Mapping with Hyponym Set as well


urllib.urlretrieve("http://image-net.org/archive/words.txt", filename="test.txt")

