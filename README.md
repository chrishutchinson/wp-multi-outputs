# WP Multi-outputs
## v1.0.0 (Experimental)

**By Chris Hutchinson**

The beginnings of an experimental WordPress plugin to allow for modular addition of new output formats. Intended for use with third party platforms such as Apple News, Facebook Instant and AMP, but can also support any other output type.

### How does it work?

WP Multi-outputs provides a consistent approach for allowing new output formats to be added to WordPress. The plugin builds a UI, and supplies all the required hooks and filters to register new outputs. At present – although, this is set to change – WP Multi-outputs adds new links under your posts table to allow access to these outputs. _This is very likely to change in the future_.

![Example UI](Screenshot.png)

The plugin contains a base Class that all outputs should extend in order to be registered with the plugin. This class provides four core functions, a `constructor`, `prepare()`, `publish()` and `teardown()`. Over the course of these functions – which are called in this order:

- developers can configure their output format (`constructor`)
- setup a post and prepare it for output (`prepare`)
- output it to a third party service or, optionally, render new content - this is how the AMP output format works (`publish`)
- and return data to WordPress (`teardown`)

### Types of output

Not all outputs require an interface with a third party service (such as Apple News). This plugin is built to work with various different types of output format. At present these are:

- `render`: Render different HTML content and allow it to be accessed by the user using a query variable (e.g. `http://example.com/post-name?wpmo_format=amp` would return the AMP version of your page to your readers)
- `output`: Output a string of text or some information on publish. The JSON output bundled in this plugin is an example of this, which just returns a JSON representation of the supplied post
- `push`: This is intended to push your post to a third party service. It returns a success message in the WordPress admin panel. The `outputs/AppleWpMultiOutput.php` example provides a demonstration of how this might work in practice.

### Available Output Formats

#### Bundled
- JSON `outputs/JsonWpMultiOutput.php`
- Apple News `outputs/JsonWpMultiOutput.php` (_Incomplete, in fact, not done at all really_)

#### Plugins
- AMP [`chrishutchinson/wpmo-AmpWpMultiOutput`](http://github.com/chrishutchinson/wpmo-AmpWpMultiOutput) (_Heavily based on the `github.com/automattic/amp-wp` plugin_)

### Things to do

- [ ] Complete the Apple News output and extract to a standalone plugin
- [ ] Tidy up the interface in WordPress, providing a UI similar to the themes or plugins view for installing new output formats
- [x] Speak to the WordPress community about how we can make this better
- [ ] Document the creation of new outputs
- [ ] Make dinner
- [ ] Write some tests
- [ ] Implement a Facebook Instant output and create as a plugin