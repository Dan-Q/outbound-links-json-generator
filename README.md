# .well-known/links generator for WordPress

## What is this?

Explanatory blog post: https://danq.me/well-known-links-in-wordpress
Original concept and implementation: https://blog.jim-nielsen.com/2022/well-known-links-resource/

`.well-known/links` is a proposal for websites to be able to list their _outgoing_ links in a JSON format at a well-known URL.

This code implemnents a basic plugin to add this functionality to [WordPress](https://wordpress.org/).

## How do I use it?

You will need WordPress and [WP-CLI](https://wp-cli.org/).

1. Download `outbound_links_json_generator.php` directly into your `wp-content/plugins/` directory, e.g. `https://raw.githubusercontent.com/Dan-Q/outbound-links-json-generator/main/outbound_links_json_generator.php > wp-content/plugins/outbound_links_json_generator.php`
2. Activate the `outbound_links_json_generator` plugin, e.g. `wp plugin activate outbound_links_json_generator`
3. Run `wp outbound-links` to generate your `.well-known/links` file (you might like to set this up on a `cron` job)

Depending on your webserver configuration, you might need to ensure that this file is served with `Content-type: application/json`. in nginx, for example, you might add the following configuration:

```
  location ~ /.well-known/links {
    default_type application/json;
  }
```

### Isn't that a bit basic?

Yes! Ideally this could be expanded into a more full-featured plugin with a GUI, wp-cron functionality, maybe triggers on post save/update. There's lots more ideas too; see the source code for some notes.

## Contributing

If you're excited by this idea, send me a pull request; I'm more than happy to collaborate on this!
