# Moodle plugin tool_asynccourseimport

This is a Moodle admin/tool plugin that imports a big number of courses from a CSV file using asynchronous scheduled 
tasks to avoid any browser's timeout errors.

## Pre-requisites: 

* Having access to the server's shell is mandatory.
* For this plugin to work properly, it is better to have the cron process well configured on the server, although the 
asynchronous tasks processing might be launched by running:
 
    * ```sudo php ./admin/cli/cron.php``` to run all the cron tasks.
    * or ```sudo php ./admin/tool/task/cli/adhoc_task.php  --execute``` for a specific adhoc task processing.
      

## Installation:

You can choose any of the following methods. As a convention all the commands are written as executed from the root directory of your Moodle installation.
 
### Method 1: Using GIT 

   * Go to the Moodle's installation root folder.
   * Clone the plugin's repo: ```git clone https://github.com/liip-elearning/asynccourseimport.git ./admin/tool/asynccourseimport```
   * Go to your website's administration section (e.g: http://yourmoodlesite.ch/admin).
   * Being all the installation requirements fulfilled, just click on the **"Upgrade Moodle database now"** button.
   * You will receive the installation success message and that's it for the installation procedure.
        
### Method 2: Uncompressing the Zip file 
 
   * Go to the Moodle's installation root folder.
   * Unzip the plugin file into the moodle's directory *./admin/tool/* 
   * Go to your website's administration section (e.g: http://yourmoodlesite.ch/admin).
   * Being all the installation requirements fulfilled, just click on the **"Upgrade Moodle database now"** button.
   * You will receive the installation success message and that's it for the installation procedure.
    
### Method 3: Using the plugins interface on the Moodle's admin page (web)
    
   * Go to your website's plugin administration section: (e.g: http://yourmoodlesite.ch/admin/tool/installaddon/index.php).
   * Upload the zip file.
   * Click on the **"Install plugin from the ZIP file"** button.
   * Moodle will verify if the plugin can be installed, at this point if you get an error of access permission, run: ```chmod -R 777 ./admin/tool``` and retry the ZIP uploading.
   * After the verifications click on the **"Continue"** button.
   * Now that all the installation requirements have been fulfilled, just click on the **"Upgrade Moodle database now"** button.
   * You will receive the installation success message and that's it for the installation procedure.
   

## Usage:

* Go to http://yourmoodlesite.ch/admin/tool/asynccourseimport/index.php, also available on the *"Course"* tab of the site administration section as "Asynchronous course import".
* From here, the process is the same as the standard course import (https://docs.moodle.org/37/en/Upload_courses). Except that the outcome is not the import itself but a scheduled task that will perform the import.
    * Upload one CSV file
    * Follow the process (setting options, click on preview & upload courses).
    * See a recap of the errors, and a confirmation message.

At this point everything else should be handled by the **cron process**, otherwise, the commands to start performing the
async deletion tasks can be launched with the commands stated on the *"Pre-requisites"* section of this document.


#### Logs and Notices
 
The course import process will perform one scheduled task for each CSV file, if it encounters any error, the task will fail, and will be automatically rescheduled up to 3 times. 
By the end of the trials, one entry on the Moodle's standard log will be added, so it can be found on: http://yourmoodlesite.ch/report/log/index.php, 
where you can choose the *"CLI"* as the source of the entries in order to filter the messages.

At the end of the scheduled tasks processing, the system will issue a notification of success or failure with all the related details,
accessible via the bell icon on the header's menu or pointing the browser to http://yourmoodlesite.ch/message/output/popup/notifications.php

  
## Uninstallation:

The best way to unistall the plugin is:

* On the shell go to the Moodle's installation root folder and remove the plugin folder (e.g: ```rm -rf ./admin/tool/asynccourseimport/```
* Point the browser to: http://yourmoodlesite.ch/admin/index.php there, you will see the status of the plugins is *"Missing from disk"*.
* Click on the **"Upgrade Moodle database now"** button.
* Then, browse to the plugins overview page: http://yourmoodlesite.ch/admin/plugins.php
* Search for *"asynccourseimport"*, you must find one line related to the plugin.
* Click on the **"Uninstall"** link and the interface will guide you through the rest of the process.
 
  
 ## Got any feedback?
  
 Please do not hesitate to contact us at elearning@liip.ch
 
 Happy asynchronous bulk course import!