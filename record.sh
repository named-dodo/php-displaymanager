#!/bin/bash

# Capture the first framebuffer and write it to a file.
ffmpeg -f fbdev -i /dev/fb0 recording_`date -Iseconds`.mp4

# to get it into a streaming service (discord?), you can use the command below.
# It has a delay and is laggy and buggy, but it works for me.
# ffmpeg -f fbdev -i /dev/fb0 -f matroska - | ffplay -noborder -

