## CF Author Levels

- The CF Author Levels plugin gives site admins the ability to create lists of authors to be displayed on the site and also adds user meta information for each user

### User Meta Addition Information

- The plugin also adds user fields to the user edit screen for adding more information about the authors
	1.  The plugin removes the standard built in WP "Biographical Info" section of the user edit screen
	2.  The plugin adds the following items by default
		- User Bio:
			1.  This section is an HTML field for entering in a bio for a user
			2.  This section is different for each blog in place, but Bios can be transferred from other blogs to this section using the built in area below the edit area that displays other Blogs bios
		- Photo URL:
			1.  This is a field for entering a URL to a photo to be displayed in the Lists of authors
			2.  This field is the same across all blogs, it is not unique to the current blog
			3.  This field is optional, a standard mystery image is in place
			4.  The image entered here should have the dimensions of 80px x 110px, all images larger than 80px will be resized to 80px wide automatically
				- This image can be a link to another site like Flickr or a service similar or any other website with an image
					1.  This should always be an absolute URL to the image
				- This image can also be placed on the current server in the /wp-content/author-photos/ folder inside the web root
					1.  If the image is in this folder, all that is needed is the file name, the plugin will add the rest
		- Feedburner RSS Link
			1.  This section is a link to the RSS feed for the current user
			2.  This section is unique for every site
			3.  This section will tie in to the CF Links plugin and change the "author_rss" link for the current user to the value in this field
			
### Author Lists

- The plugin also has the ability to create and display lists of users

#### Author List creation

- To create a new list of users:
	1.  Log in to the WP Admin
	2.  Under the Settings section of the Admin navigation, click the CF Author Levels link
	3.  In the navigation at the top of the screen click the List Types link
	4.  The current list types will be displayed, click the Add New List Type button to create a new list
	5.  Enter a name into the Name field, and a description into the Description area
		- Both the name and description can be used on the front end display if desired
	6.  Order the lists as needed by clicking the Up/Down arrow and dragging the list to the desired position
		- The order of the lists is only important if the entire group of lists is going to be displayed
	7.  Click the Update Settings button to save changes
- To add users to a list:
	1.  Log in to the WP Admin
	2.  Under the Settings section of the Admin navigation, click the CF Author Levels link
	3.  In the navigation at the top of the screen click the Lists link
	4.  Click the Add New User button in the list where the user should be added
	5.  Select the user from the drop down list displayed
	6.  Move the new user using the Up/Down arrow to the desired position
	7.  Click the Update Settings button to save changes
- To remove users from a list:
	1.  Log in to the WP Admin
	2.  Under the Settings section of the Admin navigation, click the CF Author Levels link
	3.  In the navigation at the top of the screen click the Lists link
	4.  Click the Delete button next to the name of the user to remove from the list
	5.  Click the Update Settings button to save changes

#### Display Author Lists

- To display author lists on the front end of the site, Template tags must be added to the theme.  
- To display all of the author lists

<div><?php echo cfum_get_levels(); ?></div>

- To display a single list of authors

<div><?php echo cfum_get_level('list-key-here'); ?></div>

- To get the list key, navigate to the List Types page, under the Name of the list will be the key