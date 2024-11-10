<?php

class CatGramTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        update_option('cat_gram_api_key', 'test_key');

        add_filter('pre_http_request', [$this, 'mock_http_response'], 10, 3);
    }

    public function mock_http_response($preempt, $parsed_args, $url)
    {
        $breeds = [
            'pers' => ['url' => 'https://example.com/cat-persian.jpg', 'description' => 'Persian'],
            'beng'  => ['url' => 'https://example.com/cat-ben.jpg', 'description' => 'Bengals'],
        ];

        foreach ($breeds as $breed_id => $breed_data) {
            if (strpos($url, "breed_ids=$breed_id")) {
                return [
                    'headers' => [],
                    'body'    => json_encode([
                        [
                            'url' => $breed_data['url'],
                            'breeds' => [
                                ['description' => $breed_data['description']]
                            ]
                        ]
                    ]),
                    'response' => [
                        'code'    => 200,
                        'message' => 'OK',
                    ],
                ];
            }
        }

        return $preempt;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        delete_option('cat_gram_api_key');
    }

    /**
     * Test if no breed attr is present.
     */
    public function testDefaultBreed()
    {
        $output = do_shortcode('[cat_gram]');
        $this->assertStringContainsString('<img', $output,  'The default breed photo Persian should be generated.');
        $this->assertStringContainsString('Persian', $output, 'The image alt should contain the breed name Persian.');
    }

    /**
     * Test if specific breed is present.
     */
    public function testSpecificBreed()
    {
        $output = do_shortcode('[cat_gram breed="beng"]');
        $this->assertStringContainsString('<img', $output, 'The specific breed photo bengals should be generated.');
        $this->assertStringContainsString('Bengals', $output, 'The image alt should contain the breed name Bengals.');
    }

    /**
     * Test if class will be inserted in tag.
     */
    public function testClassAttr()
    {
        $output = do_shortcode('[cat_gram breed="beng" class="cat-img"]');
        $this->assertStringContainsString('cat-img', $output, 'The image should have a class name.');
    }

    /**
     * Test if no class attr present.
     */
    public function testNoClassAttr()
    {
        $output = do_shortcode('[cat_gram breed="beng"]');
        $this->assertStringNotContainsString('class=', $output, 'The image should not have a class attribute if none is provided.');
    }

    /**
     * Test if breed is present but no value and will still output default breed.
     */
    public function testEmptyBreed()
    {
        $output = do_shortcode('[cat_gram breed=""]');
        $this->assertStringContainsString('<img', $output, 'Even with an empty breed attr, the default breed should be inserted.');
        $this->assertStringContainsString('Persian', $output, 'The image alt should contain the breed name Persian.');
    }
}
