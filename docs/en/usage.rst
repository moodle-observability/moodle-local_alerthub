Usage
=====

Plugins raise alerts through the API, as an optional dependency::

    if (class_exists('\local_alerthub\api')) {
        \local_alerthub\api::raise([
            'source' => 'tool_myplugin',
            'dedupkey' => 'disk',
            'severity' => 'critical',      // info, warning or critical.
            'title' => 'Disk almost full',
            'body' => '95% used',
            'url' => 'https://example.com/admin',
        ]);
        \local_alerthub\api::resolve('tool_myplugin', 'disk');
    }

The alerts page lists the active and the resolved alerts.
