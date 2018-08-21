## Screenshot

![Screenshot](https://raw.github.com/xcartmods/simple-file-manager/master/screenshot.jpg "Screenshot")

![Screenshot](https://raw.github.com/xcartmods/simple-file-manager/master/screenshot2.jpg "Screenshot")

![Screenshot](https://raw.github.com/xcartmods/simple-file-manager/master/screenshot_3.jpg "Screenshot")

![Screenshot](https://raw.github.com/xcartmods/simple-file-manager/master/screenshot_4.jpg "Screenshot")

![Screenshot](https://raw.github.com/xcartmods/simple-file-manager/master/screenshot_5.jpg "Screenshot")

# ADDITIONS...

- New security setting - $THIS_FILENAME
- New security setting - $PASSWORD_STRONG
- New session ID
- Bootstrap v4, Bootswatch themes ($bootswatch_theme), responsive
- New login form, default password = ch@ng3me123$
- Images replaced with FontAwesome icons
- Icons for specific file types
- Modals for image, video and audio file types, all other files types load in new tab
- Optional advanced lightbox for image files ($lightgallery)
- Delete file confirm dialog setting ($delete_confirm)
- Full width layout setting ($full_width)
- Home, refresh and logout buttons
- Tested with PHP v7.2

---

simple-file-manager
===================

A Simple PHP file manager.  The code is a single php file.  

Just copy `index.php` to a folder on your webserver.  

## Why it is good

- Single file, there are no images, or css folders.  
- Ajax based so it is fast, but doesn't break the back button
- Allows drag and drop file uploads if the folder is writable by the webserver (`chmod 777 your/folder`)
- Suits my aesthetics.  More like Dropbox, and less like Windows Explorer
- Works with Unicode file names
- The interface is usable from an iPad
- XSRF protection, and an optional password.

## Do not allow uploads on the public web

If you allow uploads on the public web, it is only a matter of time before your server is hosting and serving very illegal content. Any of the following options will prevent this:
 - Don't make the folder writable by the webserver `chmod 775`
 - Set `$allow_upload = false`
 - Use a password `$PASSWORD = 'some password'`
 - Use a `.htaccess` file with Apache, or `auth_basic` for nginx
 - Only use this on a private network

HT: [@beardog108](https://github.com/beardog108)

## Forks

- **Edit feature**. An extension of the initial project which lets you edit files and save them from the main php file. Works asynchronously with ajax requests. Link: [@diego95root](https://github.com/diego95root/File-manager-php)


