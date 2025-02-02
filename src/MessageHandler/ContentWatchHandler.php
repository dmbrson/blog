<?php
namespace App\MessageHandler;

use App\Message\ContentWatchJob;
use App\Repository\BlogRepository;
use App\Service\ContentWatchApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ContentWatchHandler
{
    public function __construct(
        private ContentWatchApi        $contentWatchApi,
        private EntityManagerInterface $em,
        private BlogRepository         $blogRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(ContentWatchJob $contentWatchJob): void
    {
        $blogId = (int)$contentWatchJob->getContent();
        $blog = $this->blogRepository->find($blogId);

        $blog->setPercent(
            $this->contentWatchApi->checkText($blog->getText())
        );

        $this->em->flush();
    }
}