Example of CMS (Content Management System) using just PHP (no database)
for displaying HTML content from UTF-8 text files and offering

* sorting by visits / date
* taxonomies (key words assigned to concrete articles)
* filtering HTML (you can select, which tags are allowed in content)
* visits counter (feature requires SQLite)
* mobile version
* dark mode
* redirections
* menu with Top 10, history (pages from concrete months) & taxonomy terms
* teasers (summary of article in the page list) different than content
* comments (currently only viewing, no adding or editing)
* tools for migrating from Drupal (Pushbutton) and checking if content
  has got dead links

and much more.

Advantages:

* extremly small and fast
* easy to understand and very portable (no database)
* flexible
* JavaScript is used only for marking internal links (you can treat it
  as optional feature)
* cookies are used only for marking if mobile version and dark theme are on

Current theme is similar to Pushbutton from Drupal.

Example of implementation on the www.mwiacek.com.

License

This is experimental project and I'm thinking about license now.

For using for serving webpages - feel free to take it do what you want with it as long
as you will contribute your improvements to the code. Please note, that author doesn't
take any responsibility for this code.

For using in commercial products (such like CMS) - please contact marcin@mwiacek.com

For standard license - please contact marcin@mwiacek.com