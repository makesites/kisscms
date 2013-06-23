# KISSCMS

### _Content Management made Simple_

A CMS with minimal footprint written in PHP, following best practices in MVC architecture and with main objective to jump start web development by providing a clean and extensible environment.

Current release: [v1.7](https://github.com/makesites/kisscms/archive/1.7.0.zip)
[![Build Status](https://secure.travis-ci.org/makesites/kisscms.png)](http://travis-ci.org/makesites/kisscms)

Website: http://www.kisscms.com


## Features:

* Minimal setup                                                                                                                                       
* Uncluttered administration area
* Wiki-style page creation
* Slim template engine with simplified methods 
* Copy paste for plugin installation
* Automated minification of css/js
* LESS support


## Setup

KISSCMS may be installed using Composer (getcomposer.org) or by cloning it from its Github repository. 

1. Extract the archive and place the contents of the folder where you want your website to be located. 
2. Place the files in the "html" folder should in your web root. All other folders one directory level above it.
3. Open a browser and enter the address of your website. Sample content should already work with no further effort.
4. Type "/admin" in your web address to enter the administration area. Default (username/password) are: ** admin / admin **


## Customization

The default setup is optimized for a new website with its own domain. If you want an altered setup, please open the file "html/index.php" in a text editor to modify the paths as it's apropriate. The html folder also contains web assets which you can freely modify to create your custom layout. The configuration of basic CMS options is located through the administration bar. For instructions how to extend the CMS with additional logic please refer to the "Reference" section.


## Upgrading

If you haven't modified anything in the APP folder, upgrading should be as simple as replacing the APP folder with the latest version. For added security you can rename the old app folder and refer to is as a BASE folder so it will operate as a fallback. If you need further assisctance please send a support ticket through the issue tracker on github: 
<http://github.com/makesites/kisscms/issues>


## Reference

For further information how to manage your website, customize it's features and access the methods available to you please refer to the official documentation: 
<http://kisscms.com/reference>


## Credits

Lead Developer: Makis Tracend ( [@tracend](http://github.com/tracend) )

Distributed through [Makesites.org](http://www.makesites.org)


### License

This work is released dual-licensed under the MIT/X11 license and the GNU General Public License (GPL)


