
import os
import re
from bs4 import BeautifulSoup


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
	#trainDir = '/Users/Ishan/Documents/WSD/Evaluation Datasets/Kate Saenko/bass';
	trainDir = '/Users/Ishan/Documents/WSD/Evaluation Datasets/yahoo5_all/speaker/good';
	finalPath = '/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/speaker/';
	for dirPath,dirNames, files in os.walk(trainDir):
	#format testing data i.e remove HTML tags and line breaks.
			for file in files:
					myFile  = os.path.join(dirPath,file);
					if ( myFile != "/Users/Ishan/Documents/WSD/Evaluation Datasets/yahoo5_all/speaker/good/.DS_Store"):
										fp = open(myFile,"r+");
										print myFile;
										newfile = "text_" + file.replace('html','txt');
										newfile = finalPath + newfile;
										f = open(newfile,"a")
										soup = BeautifulSoup(fp);
										text = soup.get_text();
										text = text + "\n";
										f.write(text.encode('utf-8'));



#prepareTrainData();

print "test data prepared..";
def processTestData():
	testDir = "/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/speaker";
	finalPath = "/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/parsed speaker/"
	for dirPath,dirNames, files in os.walk(testDir):
			for file in files:
					myFile = os.path.join(dirPath,file);
					if ( myFile != "/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/speaker/.DS_Store"):
								fp = open(myFile,"r");
								newfile = "final_"+file.replace('html','txt');
								newfile = finalPath + newfile;
								for line in fp:
											if not re.match(r'^\s*$', line):
												line = re.sub(r"\s+", ' ', line);
												f = open(newfile,"a");
												f.write(line);
								f.close();
										  # Remove all non-word characters (everything except numbers and letters)
			     					#line = re.sub(r"[^\w\s]", '', s);
									      # Replace all runs of whitespace with a single dash
									#line = re.sub(r"\s+", '-', s);


def get_words_from_string(s):
    return set(re.findall(re.compile('\w+'), s.lower()))


def getwordsFromTestData():
	testDir = "/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/parsed speaker";
	finalPath ="/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/final_words_speaker/"
	for dirPath,dirNames, files in os.walk(testDir):
			for file in files:
					myFile = os.path.join(dirPath,file);
					print myFile;
					if ( myFile != '/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/parsed speaker/.DS_Store'):
								fp = open(myFile,"r");
								newfile = "words_"+file.replace('html','txt');
								newfile = finalPath + newfile;
								for line in fp:
										search_words = get_words_from_string(line);
										newline  = ' '.join(search_words);
										f = open(newfile,"a");
										f.write(newline);
								f.close();


#prepareTestData();
#processTestData();
#print "test data processed fetching words!"
#getwordsFromTestData();


def mergeFInalWords():
	srcDir = "/Users/Ishan/Documents/WSD/Evaluation Datasets/Dr. Saneko Parsed Dataset/final_words_bass/";
	f = open("test.txt","a");
	for dirPath, dirNames, files in os.walk(srcDir):
		for file in files:
				myFile = os.path.join(dirPath,file);
				fp = open(myFile,"r");
				for line in fp:
						line = file + " " + line ;
						line = line + "\n";
						f.write(line);


mergeFInalWords();










