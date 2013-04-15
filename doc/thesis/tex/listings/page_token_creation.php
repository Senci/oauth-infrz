$p_token = $this->getUniqueHash('page_token', 'token');
$insert_token = 'INSERT INTO page_token (user_id, token, expires_at)
                       VALUES (:user_id, :token, :expires_at);';
$stmt = $this->db->prepare($insert_token);
$stmt->bindParam('user_id', $user->id);
$stmt->bindParam('token', $p_token);
$stmt->bindValue('expires_at', time()+(30*60));
$stmt->execute();
