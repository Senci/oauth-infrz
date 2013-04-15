$page_token = $_POST['page_token'];
$page_token = $this->db->getPageTokenByToken($page_token);
$user = $this->authFactory->getUser();
if (!$page_token or !$user or $page_token->user_id != $user->id) {
    $this->responseBuilder->buildError('no_permission');
}
if ($page_token->expires_at <= time()) {
    $e = 'Unfortunately your Page-Token has expired! Please go back, '.
           'reload the page and try again';
    $this->responseBuilder->buildError('no_permission', $e);
}
$this->db->deletePageToken($page_token->token);