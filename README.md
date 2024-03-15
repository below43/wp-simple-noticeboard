# wp-simple-noticeboard
A simple, free, noticeboard plugin for Wordpress

## Installation

1. Download the plugin from the GitHub repository.
2. Upload the plugin to the `/wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### Slug
Make sure the "notices" slug is free for this plugin to use.

### Resource posts in your theme
Copy the theme samples from this project to your theme folder (remove the "sample-" prefix).

After creating this file, WordPress should use it to display your 'notices' custom post type. If you're still having issues, make sure to flush your permalinks by going to "Settings" -> "Permalinks" and clicking "Save Changes".

Note that category.php is generic across an entire category, so if you have other custom post types, you'll need to edit the if statement.


### Shortcode

You can use the `[display_noticeboard_list]` shortcode to display a specific vacancy item. The shortcode accepts the following attributes:

- `category`: The category you wish to filter on (optional)

Example usage:

```markdown
[display_noticeboard_list category="notices"]
```



## Changelog

### 1.0
- Initial release

## Contributing

Contributions are welcome! To contribute, fork the repository, make your changes, and submit a pull request.

## License

This project is licensed under the terms of the MIT license. For more information, see the `LICENSE` file in the project directory.