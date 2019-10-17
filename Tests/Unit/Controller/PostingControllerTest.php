<?php
namespace ITX\Jobs\Tests\Unit\Controller;

/**
 * Test case.
 *
 * @author Stefanie Döll 
 * @author Benjamin Jasper 
 */
class PostingControllerTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \ITX\Jobs\Controller\PostingController
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(\ITX\Jobs\Controller\PostingController::class)
            ->setMethods(['redirect', 'forward', 'addFlashMessage'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function listActionFetchesAllPostingsFromRepositoryAndAssignsThemToView()
    {

        $allPostings = $this->getMockBuilder(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postingRepository = $this->getMockBuilder(\ITX\Jobs\Domain\Repository\PostingRepository::class)
            ->setMethods(['findAll'])
            ->disableOriginalConstructor()
            ->getMock();
        $postingRepository->expects(self::once())->method('findAll')->will(self::returnValue($allPostings));
        $this->inject($this->subject, 'postingRepository', $postingRepository);

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $view->expects(self::once())->method('assign')->with('postings', $allPostings);
        $this->inject($this->subject, 'view', $view);

        $this->subject->listAction();
    }

    /**
     * @test
     */
    public function showActionAssignsTheGivenPostingToView()
    {
        $posting = new \ITX\Jobs\Domain\Model\Posting();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('posting', $posting);

        $this->subject->showAction($posting);
    }

    /**
     * @test
     */
    public function createActionAddsTheGivenPostingToPostingRepository()
    {
        $posting = new \ITX\Jobs\Domain\Model\Posting();

        $postingRepository = $this->getMockBuilder(\ITX\Jobs\Domain\Repository\PostingRepository::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $postingRepository->expects(self::once())->method('add')->with($posting);
        $this->inject($this->subject, 'postingRepository', $postingRepository);

        $this->subject->createAction($posting);
    }

    /**
     * @test
     */
    public function editActionAssignsTheGivenPostingToView()
    {
        $posting = new \ITX\Jobs\Domain\Model\Posting();

        $view = $this->getMockBuilder(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface::class)->getMock();
        $this->inject($this->subject, 'view', $view);
        $view->expects(self::once())->method('assign')->with('posting', $posting);

        $this->subject->editAction($posting);
    }

    /**
     * @test
     */
    public function updateActionUpdatesTheGivenPostingInPostingRepository()
    {
        $posting = new \ITX\Jobs\Domain\Model\Posting();

        $postingRepository = $this->getMockBuilder(\ITX\Jobs\Domain\Repository\PostingRepository::class)
            ->setMethods(['update'])
            ->disableOriginalConstructor()
            ->getMock();

        $postingRepository->expects(self::once())->method('update')->with($posting);
        $this->inject($this->subject, 'postingRepository', $postingRepository);

        $this->subject->updateAction($posting);
    }

    /**
     * @test
     */
    public function deleteActionRemovesTheGivenPostingFromPostingRepository()
    {
        $posting = new \ITX\Jobs\Domain\Model\Posting();

        $postingRepository = $this->getMockBuilder(\ITX\Jobs\Domain\Repository\PostingRepository::class)
            ->setMethods(['remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $postingRepository->expects(self::once())->method('remove')->with($posting);
        $this->inject($this->subject, 'postingRepository', $postingRepository);

        $this->subject->deleteAction($posting);
    }
}
