# WRC-Secure-WPEngine

Name: WRC Secure WordPress Theme
Description: Theme for the secure site.
Developer: Integrity St. Louis

Note: Changelog started 9/3/2019 after theme has been in productin for a few years. This is document is to capture theme changes moving forward.

Usage: Create an entry with ## Verion X.X.X - update the theme version in sass/style.scss. Include date and developer under this heading. Add changes with ### and appropriate label.

## Version 1.0.7
Date 05/14/2020
Developer: Norman Huelsman

### Updated
- modal-ll-form.php - updated gravity form id for WLL feedback form
- learning-library-list-template.php - add intro text and videos before library items
- _learning_library.scss - styles for the intro section


# Version 1.0.6
Date 05/12/20
Developer: Julia Cramer

### Updated
- Fix for news and learning library header issues on IE

## Version 1.0.6
Date 04/16/20
Developer: Julia Cramer

### Updated
- Added ACFs for full width template to add left/right column info and form shortcode
- Updated CSS to accomodate 1 and 2 column layouts

## Version 1.0.6
Date 04/14/20
Developer: Julia Cramer

### Updated
- learnging-library & news - Moved all cpt and post titles off of featured image and add below
- sidebar nav > Removed The WiRe link to news and instead added a hard link to The Wire newsletter on Marketing
- full-width > Added full width template and styles
- Learning Library > removed the "launch program" button from learning library list page

## Version 1.0.6
Date 03/19/2020
Developer: Norman Huelsman

### Updates
- functions.php register our version of jQuery, and disable autocomplete 
- page-login.php add id user pass to the password field

### Add
- jQuery 3.4.1 to js folder- prevent file access plugin

## Version 1.0.5
Date: 03/18/2020
Developer: Norman Huelsman

### Hotfix
- content-news-header.php on line 14 - comment out the div that contains the pdf shortcode used by the removed pdf plugin

### Updates 
The site was updated for WP Core and plugins, mainly removing dk-pdf-php7 plugin because of security concerns.


## Version 1.0.4
Date: 03/03/2020
Developer: Norman Huelsman

### Update
- various small changes to learning library pages
- remove load more button on library items when not needed
- remove tags from library archive page and make sure search will load tags (taxonomy terms) in the search


## Version 1.0.3
Date: 01/06/2020
Developer: Norman Huelsman

### Update

- page-favorites.php ensure the favorites list loads even if the current site site doesn't have a favorite. 

## Version 1.0.2
Date: 12/2/2019
Developer: Norman Huelsman

### Notes
Revisions to the learning library section. There has been some back and forth between the favorites list function. Adding styles for the learning library meta info and links etc. 


## Version 1.0.1
Date: 9/3/2019
Developer: Norman Huelsman

### Added 
- changelog.md

### Updated
- template-parts > content-login-rightnav.php comment out MLPA registration info - added note that this type of content needs to be added through ACF
- theme version number in style.css