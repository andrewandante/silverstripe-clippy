<?php

namespace SilverStripe\Clippy\Converter;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter as CommonMarkConverter;
use SilverStripe\Core\Config\Configurable;

class MarkdownConverter
{
    use Configurable;

    private CommonMarkConverter $converter;
    
    private static array $default_config = [
        'html_input' => 'strip',
        'allow_unsafe_links' => true,
        'heading_permalink' => [
            'html_class' => '',
            'id_prefix' => '',
            'apply_id_to_heading' => true,
            'fragment_prefix' => '',
            'symbol' => '',
        ],
    ];

    public function __construct(
        private array $config = [],
    ) {
        $combinedConfig = array_merge(static::config()->get('default_config'), $config);
        // Configure the Environment with all the CommonMark parsers/renderers
        $environment = new Environment($combinedConfig);
        $environment->addExtension(new CommonMarkCoreExtension());

        // Add the other extensions you need
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $this->converter = new CommonMarkConverter($environment);
    }

    public function convert(string $input)
    {
        return $this->converter->convert($input);
    }
}