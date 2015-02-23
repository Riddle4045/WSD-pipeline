CPP_FILES := $(wildcard src/*.cpp)
OBJ_FILES := $(addprefix obj/,$(notdir $(CPP_FILES:.cpp=.o)))
LD_FLAGS := -L./lib
CC_FLAGS := -I./include -w
OUT := bin/proc
$(OUT): $(OBJ_FILES)
	mpic++ -o $@ $^ $(LD_FLAGS)
obj/%.o: src/%.cpp include/%.h
	mpic++ $(CC_FLAGS) -c -o $@ $<
clean:
	rm obj/* bin/* 2> /dev/null || true
