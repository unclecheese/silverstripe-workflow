<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Workflow\WorkflowService;
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowServiceFactoryTest extends SapphireTest
{
    public static $extra_dataobjects = [
        TestObject::class,
    ];
    
    /**
     * Test factory creation of Workflows.
     */
    public function testInjection() {
        $workflow = Injector::inst()->get('MyWorkflow');

        $obj = new TestObject();
        $this->assertTrue(WorkflowService::registry()->has($obj, 'MyWorkflow'));
        $workflow = WorkflowService::registry()->get($obj);

        $markingStore = $workflow->getMarkingStore();
        $this->assertInstanceOf(DataObjectMarkingStore::class, $markingStore);
        $places = $workflow->getDefinition()->getPlaces();
        $this->assertContains('draft', $places);
        $this->assertContains('reviewed', $places);
        $this->assertContains('rejected', $places);
        $this->assertContains('published', $places);

        $transitions = $workflow->getDefinition()->getTransitions();
        $this->assertCount(3, $transitions);

        $this->assertContains('draft', $transitions[0]->getFroms());
        $this->assertContains('reviewed', $transitions[1]->getFroms());
        $this->assertContains('reviewed', $transitions[2]->getFroms());

        $this->assertContains('reviewed', $transitions[0]->getTos());
        $this->assertContains('published', $transitions[1]->getTos());
        $this->assertContains('rejected', $transitions[2]->getTos());

        $this->assertTrue($workflow->can($obj, 'to_review'));
        $this->assertFalse($workflow->can($obj, 'publish'));

        $workflow->apply($obj, 'to_review');

        $this->assertTrue($workflow->can($obj, 'publish'));
        $this->assertTrue($workflow->can($obj, 'reject'));
    }
}