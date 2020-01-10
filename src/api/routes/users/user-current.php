<?php

use Slim\Http\Request;
use Slim\Http\Response;
use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\UserQuery;


$app->group('/user/current', function () {
    $this->post("/settings/show/finance", "updateSessionFinance");
    $this->post("/refresh2fasecret", "refresh2fasecret");
    $this->post("/refresh2farecoverycodes", "refresh2farecoverycodes");
    $this->post("/remove2fasecret", "remove2fasecret");
    $this->post("/test2FAEnrollmentCode", "test2FAEnrollmentCode");
    $this->get("/get2faqrcode",'get2faqrcode');
});

function updateSessionFinance(Request $request, Response $response, array $args)
{
    $user = AuthenticationManager::GetCurrentUser();

    if ($request->getContentLength() > 0) {
        $setting = (object)$request->getParsedBody();
        $user->setShowPledges(ConvertToBoolean($setting->pledges));
        $user->setShowPayments(ConvertToBoolean($setting->payments));
        $user->setShowSince($setting->since);
        $user->save();
    }

    return $response->withJson([
        "user" => $user->getName(),
        "userId" => $user->getId(),
        "showPledges" => $user->isShowPledges(),
        "showPayments" => $user->isShowPayments(),
        "showSince" => $user->getFormattedShowSince()
    ]);

}


function refresh2fasecret(Request $request, Response $response, array $args)
{
    $user = AuthenticationManager::GetCurrentUser();
    $user->Regenerate2FAKey();
    return $response->withJson(["TwoFAQRCodeDataUri" => $user->getTwoFactorAuthQRCodeDataUri()]);
}

function refresh2farecoverycodes(Request $request, Response $response, array $args)
{
    $user = AuthenticationManager::GetCurrentUser();
    return $response->withJson(["TwoFARecoveryCodes" => $user->getNewTwoFARecoveryCodes()]);
}

function remove2fasecret(Request $request, Response $response, array $args)
{
    $user = AuthenticationManager::GetCurrentUser();
    $user->remove2FAKey();
    return $response->withJson(["TwoFAQRCodeDataUri" => $user->getTwoFactorAuthQRCodeDataUri()]);
}

function get2faqrcode(Request $request, Response $response, array $args)
{
    $user = AuthenticationManager::GetCurrentUser();
    $response = $response->withHeader("Content-Type", "image/png");
    return $response->write($user->getTwoFactorAuthQRCode()->writeString());
}

function test2FAEnrollmentCode(Request $request, Response $response, array $args)
{
    $requestParsedBody = (object)$request->getParsedBody();
    $user = AuthenticationManager::GetCurrentUser();
    return $response->withJson(["IsEnrollmentCodeValid" => $user->isTwoFACodeValid($requestParsedBody->enrollmentCode)]);
}