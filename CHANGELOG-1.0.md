CHANGELOG for 1.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.0 minor versions.

* **1.0.15** *(20xx-xx-xx)*


* **1.0.14** *(2020-02-17)*
    * Bugfix delete form out of the box
    * Bugfix css progress bar for upload image modal
    * Activate allowClear option on select2 

* **1.0.13** *(2020-02-03)*
    * Bugfix CSS awesomefont

* **1.0.12** *(2020-02-03)*
    * Separate vendor & theme assets twig blocks
    * Bugfix multiple pickadate instances

* **1.0.11** *(2020-02-03)*
    * Active select for years & months in datepicker
    * Customize datepicker as material design datepicker
    * Use & customize jquery clockpicker for time picking
    * Bugfix PublishableTrait if status property isn't defined

* **1.0.10** *(2020-01-17)*
    * Fix v1.0.10 Changelog

* **1.0.9** *(2020-01-17)*
    * Remove filter & crop getter & setters in AttachImage
    * Add PublishableTrait for publishable entities

* **1.0.8** *(2019-12-18)*
    * Add public/private notion for files
    * Bugfix remove files from library

* **1.0.7** *(2019-12-18)*
    * Bugfix insert files in ckeditor

* **1.0.6** *(2019-12-05)*
    * Use ManagerRegistry instead RegistryInterface for repositories

* **1.0.5** *(2019-11-13)*
    * Bugfix rendering non required image in form
    * Bugfix rendering crops modal
    * Bugfix rendering image title x-editable form

* **1.0.4** *(2019-11-08)*
    * Make menu more customizable (add weight parameter)
    * Change path for ckeditor custom config

* **1.0.3** *(2019-11-07)*
    * Bugfix: ImageType was required by default

* **1.0.2** *(2019-11-06)*
    * Bugfix: attachImage action remove card footer content

* **1.0.1** *(2019-11-06)*
    * Make form control enabled even when there's no tabs in your form
    * Bugfix: a new form alert was displayed each time submit button was clicked 
    * Add required option in ImageType

* **1.0.0** *(2019-10-24)*
    * Introduce target entities resolver system with compiler pass
    * Externalize entities doctrine mapping with XML config files
    * Prefix bundle tables names with "aropixel_"
