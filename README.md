# The PHP displaymanager project

This project started off as a joke:
You can't make a display manager in PHP, right?
Well, as it turns out, you can!

That is what this project is.
A display manager (+window manager) inside PHP.



## How to run:
1. Be part of the `video` and `input` groups. (or be root)
2. Have PHP installed. (My version is PHP 7.4.3)
3. Get a copy of this code on your disk.
4. Go to the first TTY and log in. (Control+Alt+F1)
5. Navigate to the folder containing this README.
6. Adjust the resolution in `displaymanager.php` accordingly.
7. Run ./start.sh (You can abort by pressing the scroll wheel.)


## Rules
* No using fancy plugins or load extra modules.
* No Objects either, keep it strictly good old original PHP.
* No calling other programs. (stty and starting client programs are an exception)
* No IDE's allowed. Only text editors.
* Have fun toying around.


## TODO / Goals
* [ ] Clean up and restructure the source code.
* [ ] Finish the X11 protocol implementation.
* [ ] Optimize rendering system.
* [ ] ~~Multi-monitor support.~~ (There's only fb0?)
* [x] Automatic resolution recognision.
* [ ] Embedded terminal program.
* [ ] Flashbang mode (invert colors)
* [ ] Wayland server support?
* [ ] Multiple mouse/cursor support?

