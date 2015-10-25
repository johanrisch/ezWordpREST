# ezWordpREST

ezWordpREST is a lightweight wordpress plugin. I decided to create this plugin after doing a
couple of projects where clients needed to supply content to mobile applications. Most plugins 
out there did not supply hooks for modifying the data before they where returned.

You should be interested in this plugin if you:
  *   Want to query by post type and get the results as a JSON object in a mobile application.
  *   The data your applications are interested in is contained within the custom fields, the converters
      allow you to easily parse and verif the integrity of the custom fields before sending them to the mobile apps. You could 
      event send only the custom fields and completely ignore the other fields of the post.

I've never had the chance to do much PHP coding. If you have any suggestions on best practices and other 
improvements it's greatly appreciated!


## TL;DR show me the code
This code can be put anywhere which is executed during wordpress start up. For instance it could be put in
your themes functions.php
```php
if(function_exists("ezWordpREST")){

  // ezWordpREST requires you to explicitly allow a certain post type.
  ezWordpREST()->allowPostType("listing");
	
	// It also supplies a helper function to create post types
	// the last argument (options array) accepts the same options that
	// built in wordpress supports. 
	//
	// NOTE: ezWordpREST does NOT require you to register post types via this mehtod.
	ezWordpREST()->register_post_type( 
		'listing', __( 'Listings', 
			'ezWordpREST' ), 
		__( 'Listing', 'ezWordpREST' ),
		"A listing with nothing but a title",
		array("supports"=>array("title")) );
		

	ezWordpREST()->registerConverter("listing", function($post){
	// $post is a normal wordpress post with post type "listing" and it's custom fields loaded.
	// You may do whatever you want here, parse the custom fields to only return the necessary info
	// or simply, only return the title.
		return $post->post_title;
	});
	ezWordpREST()->registerCustomSorter("listing", function($post1,$post2){
		// If you want to rearrange the order of the posts, this is the place to do it.
		// $post1 and $post2 are the same objects as those that are returned by the 
		// listing converter.
	});

}
```
