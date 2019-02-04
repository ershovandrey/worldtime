
REQUIREMENTS
------------

World Time Clock Widget needs a jClocsGMT query plugin in "libraries" folder.


INSTALLATION
------------

1. Install the module as normal, see link for instructions.
   Link: https://www.drupal.org/documentation/install/modules-themes/modules-8

2. Download and unpack the jClocksGMT plugin in "libraries".
    Make sure the path to the plugin file becomes:
    "libraries/jclocksgmt/js/jClocksGMT.js"
   Link: https://github.com/mcmastermind/jClocksGMT/archive/master.zip
   Drush users can use the command "drush worldtime-plugin".

3. Go to "Administer" -> "Extend" and enable the World Clock Time Widget module.


Drush:
------
A Drush command is provides for easy installation of the jClocksGMT plugin.

% drush worldtime-plugin

The command will download the plugin and unpack it in "libraries/".
It is possible to add another path as an option to the command, but not
recommended unless you know what you are doing.
