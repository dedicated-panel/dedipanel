#!/bin/bash

set -e -x

sudo adduser --gecos "" --disabled-password dedipanel
echo "dedipanel:dedipanel" | sudo chpasswd
