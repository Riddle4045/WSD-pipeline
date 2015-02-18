#Inputs: root_user root_word, sense1, sense2, ...
rm -r $1/kmeans 2> /dev/null
mkdir $1/kmeans 2> /dev/null
cp -r apps/kmeans/* $1/kmeans

cnt=0

for var in "$@"
do
	cnt=`expr $cnt + 1`
	if [ $cnt -gt 2 ]
	then
		cat $2/train/$var/*.sift >> $1/kmeans/user_data/data.bin
		echo $2/train/$var
	fi
done


