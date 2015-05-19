import os
import sys
import wikipedia

path = "/home/yp/projects/data/uiuc/"
words = [["Bass (fish)", "Bass (instrument)"], ["Crane (machine)", "Crane (bird)"], ["Squash (plant)", "Squash (sport)"]]

def getWord(n):
	if n == 0:
		return "bass"
	elif n == 1:
		return "crane"
	else:
		return "squash"

for i in range( len(words)):
	wp = words[i]
	keyword = getWord(i)
	fp = path + keyword + "/txt/train.txt"
	f = open(fp, "w")
	for j in range(len(wp)):
		word = wp[j]
		print keyword, word
		pages = wikipedia.search(word)
		page = wikipedia.page(pages[0])
		print page.title
		content = page.content
		lines = content.split("\n")
		for line in lines:
			if keyword in line:
				#print "found"
				f.write(line.encode("utf8") + "\n")
	#print type(content)
	f.close()
	#print content


