Uso
===

Os plugins disparam avisos pela API, como dependência opcional::

    if (class_exists('\local_alerthub\api')) {
        \local_alerthub\api::raise([
            'source' => 'tool_meuplugin',
            'dedupkey' => 'disco',
            'severity' => 'critical',      // info, warning ou critical.
            'title' => 'Disco quase cheio',
            'body' => '95% usado',
            'url' => 'https://example.com/admin',
        ]);
        \local_alerthub\api::resolve('tool_meuplugin', 'disco');
    }

A página de avisos lista os avisos ativos e os resolvidos.
