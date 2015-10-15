#!/bin/bash

FOLDER=/tmp/cam

if ! [ -d $FOLDER ]; then
	echo "CREATE $FOLDER"
	mkdir $FOLDER
fi

raspivid -n -t 0 -sg 15000 -o $FOLDER/vid%04d.h264 -wr 5 > /dev/null 2>&1 &
