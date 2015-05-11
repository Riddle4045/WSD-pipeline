
def getAccuracy():
    test_target = [];
    result = [];
    with open("testingData.txt","r+") as f:
        for lines in f:
            line = lines.split();
            test_target.append(int(line[1][0])-1);
    print test_target
    with open("result.txt","r+") as fr:
        for lines in fr:
            result.append(int(lines));
    print result
    zeros = []
    for i in range(0,len(result)):
			if ( result[i] - test_target[i] == 0):
					zeros.append(result[i] - test_target[i]);

    print len(zeros)

getAccuracy();






