<?php

use Fisdap\Api\Client\ApiClientServiceProvider;
use Fisdap\Api\Client\Attachments\Categories\Gateway\AttachmentCategoriesGateway;
use Fisdap\Api\Client\Attachments\Gateway\AttachmentsGateway;
use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Api\Client\Gateway\CommonHttpGateway;
use Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway;
use Fisdap\Api\Client\Students\Gateway\StudentsGateway;
use Fisdap\Api\Client\Users\Gateway\UsersGateway;
use GuzzleHttp\Client;
use Illuminate\Container\Container;

require __DIR__ . '/vendor/autoload.php';

// container
$container = new Container();
$serviceProvider = new ApiClientServiceProvider($container);
$serviceProvider->register();


/*
 * Get token from IDMS
 */
$idmsClient = new Client();

$time_start = microtime(true);

$idmsResponse = $idmsClient->request('POST', 'https://id.fisdapqa.net/token', [
    'headers' => [
        'Authorization' => 'Basic ZmlzZGFwLW1vYmlsZTpPV0prTWpnNFkyWmxOVFZrWWpRd016Z3dNbU5qTVdRMQ=='
    ],
    'form_params' => [
        'grant_type' => 'client_credentials'
    ]
]);

$time_end = microtime(true);
$time = $time_end - $time_start;

$jsonResponse = json_decode($idmsResponse->getBody(), true);
$accessToken = $jsonResponse['access_token'];

echo "IDMS request took $time seconds\n";


/*
 * Bind UserAuthorization data to container
 * This would need to occur once a user role (context) is chosen
 */
$container->instance('Fisdap\Api\Client\Auth\UserAuthorization', new UserAuthorization($accessToken, 295792));


/*
 * Example User Retrieval
 */
/** @var UsersGateway $gateway */
$usersGateway = $container->make(UsersGateway::class);

$time_start = microtime(true);

$user = $usersGateway->getOneById(160982);

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "API request for User took $time seconds\n";


/*
 * Example Shift Attachments List
 */
/** @var ShiftAttachmentsGateway $shiftAttachmentsGateway */
$shiftAttachmentsGateway = $container->make(ShiftAttachmentsGateway::class);

$time_start = microtime(true);

$shiftAttachments = $shiftAttachmentsGateway->setResponseType(CommonHttpGateway::RESPONSE_TYPE_ARRAY)
    ->get(4321927, ['verifications']);

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "API request for Shift Attachments took $time seconds\n";


$remainingCount = $shiftAttachmentsGateway->getRemainingAllottedCount(295792);


//$sa = $shiftAttachmentsGateway->setResponseType(CommonHttpGateway::RESPONSE_TYPE_OBJECT)
//    ->create(4321927, 295792, 'tests/_data/cute cat wallpapers.jpg', null, null, null, ['ECG']);
//
//$psa = $shiftAttachmentsGateway->getOne(4321927, $sa->id);



/*
 * Attachment Categories
 */

/** @var AttachmentsGateway $attachmentsGateway */
$attachmentCategoriesGateway = $container->make(AttachmentCategoriesGateway::class);

/*
 * Students
 */
/** @var StudentsGateway $studentsGateway */
$studentsGateway = $container->make(StudentsGateway::class);


/*
 * Experiment
 */

eval(\Psy\sh());
