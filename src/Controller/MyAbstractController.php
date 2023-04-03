<?php declare(strict_types=1);

namespace App\Controller;

use App\Security\SteamUser;
use SBSEDV\Bundle\FormBundle\Form\FormProcessorInterface;
use SBSEDV\Bundle\ResponseBundle\Response\ApiResponseDto;
use SBSEDV\Bundle\ResponseBundle\Response\ApiResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class MyAbstractController extends AbstractController
{
    public function getSteamUser(): SteamUser
    {
        $user = $this->getUser();
        if ($user instanceof SteamUser) {
            return $user;
        }

        throw $this->createAccessDeniedException();
    }

    /**
     * Create an api response.
     *
     * @param TranslatableInterface|string|null $msg     The "msg" property.
     * @param mixed                             $data    The "data" property.
     * @param ApiResponseErrorDto[]             $errors  The "errors" property.
     * @param int                               $status  The http status code.
     * @param array                             $headers [optional] An array of headers that will be added to the response.
     * @param array                             $context [optional] Context passed to the serializer.
     *
     * @return Response An http foundation response object.
     */
    protected function apiResponse(TranslatableInterface|string|null $msg = null, mixed $data = [], array $errors = [], int $status = Response::HTTP_OK, array $headers = [], array $context = []): Response
    {
        $response = new ApiResponseDto($msg, $data, $errors, $status);

        /** @var ApiResponseFactory $responseFactory */
        $responseFactory = $this->container->get('api_response_factory');

        return $responseFactory->createApiResponse($response, $headers, $context);
    }

    /**
     * Process the form by setting the form values to the given object.
     *
     * @param FormInterface $form        The form to process.
     * @param T             $object      The object to populate.
     * @param string[]      $ignoredKeys [optional] Form keys to ignore.
     *
     * @template T of object
     */
    protected function processForm(FormInterface $form, object &$object, array $ignoredKeys = []): void
    {
        /** @var FormProcessorInterface $formProcessor */
        $formProcessor = $this->container->get('form_processor');

        $formProcessor->processForm($form, $object, $ignoredKeys);
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     */
    protected function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');

        return $translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return \array_merge(parent::getSubscribedServices(), [
            'api_response_factory' => ApiResponseFactory::class,
            'form_processor' => FormProcessorInterface::class,
            'translator' => TranslatorInterface::class,
        ]);
    }
}
