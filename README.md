# Stegify Web

## Summary

*Stegify Web* is a web front-end for [Stegify image encoding](https://github.com/DimitarPetrov/stegify) developed by Dimitar Petrov. It uses PHP to run the stegify binary on up to 3 carrier images. Although the number of images is arbitarily limited, it is easy to increase by adding more input fields to the form and changing the end number in the `for` loop. An unlimited or unspecified number of images is not possible since the order of the images must be maintained for proper decoding.

Full Demo: [https://nabasny.com/stegify/](https://nabasny.com/stegify/)

## Requirements

PHP7, jQuery 3.4.1, and the Stegify binary in the root folder. File uploads must be enabled in `php.ini` and user `www-data` needs write access to the folder.

## Screenshot

![Stegifty-Web](https://nabasny.com/stegify/screen.png)
