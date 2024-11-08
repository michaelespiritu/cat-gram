<?php

class CatGramTest extends WP_UnitTestCase
{
    /**
     * Test if no breed attr is present.
     */
    public function testDefaultBreed()
    {
        // Test shortcode with no breed attribute
        $output = do_shortcode('[cat_gram]');
        $this->assertStringContainsString('<img', $output,  'The default breed photo Persian should be generated.');
        $this->assertStringContainsString('Persian', $output, 'The image alt should contain the breed name Persian.');
    }

    /**
     * Test if specific breed is present.
     */
    public function testSpecificBreed()
    {
        // Test shortcode with a valid breed
        $output = do_shortcode('[cat_gram breed="beng"]');
        $this->assertStringContainsString('<img', $output, 'The specific breed photo bengals should be generated.');
        $this->assertStringContainsString('Bengals', $output, 'The image alt should contain the breed name Bengals.');
    }
}
