mpikmeans: mpikmeans.cpp
	mpic++ -o mpikmeans mpikmeans.cpp -lpthread -ldl
run: # run the example.
	mpirun -np 36 --hostfile user_data/hostfile mpikmeans out 2
