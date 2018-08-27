<?php
declare(strict_types=1);

/**
 * @author Tjitse (Vendic)
 * Created on 27-08-18 09:27
 */

namespace Vendic\RegenUrlKeys\Test\Unit\Model;

use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;
use Vendic\RegenUrlKeys\Model\GenerateUrlKey;

class GenerateurlKeyTest extends TestCase
{
    /**
     * @var GenerateUrlKey | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $generateUrlKey;
    /**
     * @var Product | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    public function setUp()
    {
        $this->generateUrlKey = $this->getMockBuilder(GenerateUrlKey::class)
            ->setMethods(null)
            ->getMock();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();
    }

    public function test_url_key_generation()
    {
        $this->productMock->method('getName')->willReturn('Janome cover transparante naaivoet');
        $this->assertEquals(
            'janome-cover-transparante-naaivoet',
            $this->generateUrlKey->generateUrlKey($this->productMock)
        );
    }

    public function test_url_key_generation_with_special_characters()
    {
        $this->productMock->method('getName')->willReturn('Janome cõver transparantê naaïvoet');
        $this->assertEquals(
            'janome-cover-transparante-naaivoet',
            $this->generateUrlKey->generateUrlKey($this->productMock)
        );
    }

    public function test_url_key_geneartion_with_spaces()
    {
        $this->productMock->method('getName')->willReturn('Janome cõver transparantê naaïvoet ');
        $this->assertEquals(
            'janome-cover-transparante-naaivoet',
            $this->generateUrlKey->generateUrlKey($this->productMock)
        );
    }
}
