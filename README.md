# Disable Comments

A lightweight, performance-focused WordPress plugin that completely removes the comment system. Zero configuration required: activate to disable, deactivate to restore.

## Key Features

- **Total Removal**: Strips comment and trackback support from ALL post types (public and private).
- **Functional Lockdown**: Closes comments/pings at the query level and returns empty comment arrays.
- **UI Cleanup**: Removes "Comments" and "Discussion" menus, dashboard widgets, and admin bar nodes.
- **Frontend Optimization**: 
    - Replaces theme comment templates with a blank file.
    - Deregisters the `comment-reply` script.
    - Removes comment feed links from the `<head>`.
- **Security & Performance**:
    - Strips the `X-Pingback` HTTP header.
    - Diables Pingback XML-RPC methods.
    - Unregisters the Recent Comments widget and its inline styles.

## Why this plugin?

Unlike other "Disable Comments" plugins, this one focuses on **minimal overhead**:
- **Zero Database Reads**: It does not store or check options. Activating the plugin is the toggle.
- **Zero Global Pollution**: Uses anonymous closures to avoid clashing with other plugins or themes.
- **Idiomatic Hooks**: Uses the most efficient WordPress hooks (like `admin_bar_menu` for the admin bar and `admin_init` for access control) to ensure it only runs when necessary.

## Installation

1. Upload the `disable-comments` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Done. There are no settings to configure.

## Technical Details

- **Timing**: Registers most teardown logic on `init` with priority `9999` to ensure it catches Custom Post Types registered by other plugins.
- **Admin Bar**: Uses the `remove_node` method, which is the standard, idiomatic way to modify the toolbar.
- **Compatibility**: Multisite-aware; removes comment links from the "My Sites" menu for all network sites.

## License

GPLv2 or later.
