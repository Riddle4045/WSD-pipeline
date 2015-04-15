
import os
import re


TAG_RE = re.compile(r'<[^>]+>');



#preparing the traiing data for text disambiguation.
def prepareTrainData():
	dir = '/Users/Ishan/Downloads/dataset/bass/info';
	mergedFile = open("train.txt","r+")
	for dirPath,dirnames,fileNames in os.walk(dir):
		for file in fileNames:
					file = os.path.join(dirPath, file)
					fp = open(file,"r+");
					for i, line in enumerate(fp):
								if ( i == 3):
											if 'bass' in line:
													mergedFile.write(line + "\n");
					fp.close();



def remove_tags(text):
    return TAG_RE.sub('', text)

def prepareTestData():
	trainDir = '/Users/Ishan/Documents/WSD/Evaluation Datasets/Kate Saenko/bass';
	for dirPath,dirNames, files in os.walk(trainDir):
	#format testing data i.e remove HTML tags and line breaks.
			for file in files:
					myFile  = os.path.join(dirPath,file);
					fp = open(myFile,"r+");
					f = open("test.txt","r+")
					for i,line in enumerate(fp):
								line = remove_tags(line);
								line = line.strip('\t\r');
								print line;
								f.write(line);
					fp.close();
					f.close();


prepareTrainData();
prepareTestData();