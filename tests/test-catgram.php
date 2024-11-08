<?php

class CatGramTest extends WP_UnitTestCase
{
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
