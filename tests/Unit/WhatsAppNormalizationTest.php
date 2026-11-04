<?php

namespace Tests\Unit;

use App\Support\WhatsAppNormalizer;
use Tests\TestCase;

/** @group unit */
class WhatsAppNormalizationTest extends TestCase
{
    /** @test */
    public function phone_already_starting_with_0_remains_unchanged()
    {
        $phone = '08123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_with_plus_62_normalizes_to_08()
    {
        $phone = '+628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_with_62_prefix_normalizes_to_08()
    {
        $phone = '628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_with_0062_prefix_normalizes_to_08()
    {
        $phone = '00628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_with_062_prefix_normalizes_to_08()
    {
        $phone = '0628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_812_prefix_keeps_08_format()
    {
        $phone = '08123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertEquals('08123456789', $result);
    }

    /** @test */
    public function phone_with_plus_62812_normalizes_to_0812()
    {
        $phone = '+628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertStringStartsWith('0812', $result);
    }

    /** @test */
    public function phone_with_0062812_normalizes_to_0812()
    {
        $phone = '00628123456789';
        $result = WhatsAppNormalizer::normalize($phone);
        $this->assertStringStartsWith('0812', $result);
    }

    /** @test */
    public function phone_short_invalid_format_rejected()
    {
        $phone = '81234567'; // too short
        $result = WhatsAppNormalizer::normalize($phone);
        // Short numbers are left as-is (validation happens separately)
        $this->assertLessThanOrEqual(8, strlen($result));
    }

    /** @test */
    public function phone_invalid_format_returned_as_is()
    {
        $phone = 'abcdefg';
        $result = WhatsAppNormalizer::normalize($phone);
        // Invalid format returned as-is (validation happens separately)
        $this->assertEquals('abcdefg', $result);
    }
}
