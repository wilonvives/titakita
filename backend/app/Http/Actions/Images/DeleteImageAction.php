<?php

namespace TitaKita\Http\Actions\Images;

use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Images\DeleteImageHandler;
use TitaKita\Services\Application\Handlers\Images\DTO\DeleteImageDTO;
use Illuminate\Http\Response;

class DeleteImageAction extends BaseAction
{
    public function __construct(
        public readonly DeleteImageHandler $deleteImageHandler,
    )
    {
    }

    /**
     * @throws CannotDeleteEntityException
     */
    public function __invoke(int $imageId): Response
    {
        $this->isActionAuthorized($imageId, ImageDomainObject::class);

        $this->deleteImageHandler->handle(new DeleteImageDTO(
            imageId: $imageId,
            userId: $this->getAuthenticatedUser()->getId(),
            accountId: $this->getAuthenticatedAccountId(),
        ));

        return $this->noContentResponse();
    }
}
