<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;

class TokenService
{
    private string $apiToken; 

    /** Creates a new TokenService instance with a given token from ENV 
     * variables, etc. This token will be used to validate the incoming 
     * requests Authorization header token.
     * 
     * @param string $token
     * 
     */
    public function __construct(string $token)
    {
        $this->apiToken = $token;
    }
    
    /**
     * Verifies if the provided authorization token is valid.
     * 
     * This method checks if the `Authorization` header contains a token and validates it against the stored API token.
     * 
     * @param Request|null $request The HTTP request object containing the `Authorization` header.
     * 
     * @return bool Returns `true` if the token is valid and matches the stored API token; otherwise, returns `false`.
     * 
     */
    public function isValidToken(?Request $request): bool
    {
        try{
            // TODO: Add a better token validation mechanism here
        
            $token = explode(' ', $request->headers->get('Authorization'))[1];
            return !empty($token) && $token === $this->apiToken;
        }catch(\Exception $e){
            return false;
        }
    }
}
