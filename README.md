# The PHP displaymanager project

This project started off as a joke:
You can't make a display server in PHP, right?
Well, as it turns out, you can!

That is what this project is.
A display server (+window manager) inside PHP.



## How to run:
1. Be part of the `video` and `input` groups. (or be root)
2. Have PHP installed. (My version is PHP 7.4.3)
3. Get a copy of this code on your disk.
4. Go to the first TTY and log in. (Control+Alt+F1)
5. Navigate to the folder containing this README.
6. Run ./start.sh (You can abort by pressing the scroll wheel.)


## Rules
* No using fancy plugins or load extra modules.
* No Objects either, keep it strictly good old original PHP.
* No calling other programs. (stty and starting client programs are an exception)
* No IDE's allowed. Only text editors.
* Have fun toying around.


## TODO / Goals
* [x] Automatic resolution recognision.
* [x] Wallpaper support
* [x] Embedded terminal program.
* [ ] Clean up and restructure the source code.
* [x] Optimize rendering system.
* [x] Multi-monitor support.
* [ ] Finish the X11 protocol implementation.
* [ ] Wayland server support?
* [ ] Multiple mouse/cursor support?

