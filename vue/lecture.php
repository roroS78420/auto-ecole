<?php 
auth(1); 
global $bdd; // Assure-toi que $bdd est disponible

// --- 1. LOGIQUE PHP DE LA PAGE DE LECTURE ---

$user_id = $_SESSION['id_u'];

// On vérifie qu'on a bien reçu l'ID de l'expéditeur depuis l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirige vers la boîte de réception si aucun ID n'est fourni
    header('Location: reception');
    exit();
}
$sender_id = (int)$_GET['id'];

// --- Action : Gérer l'envoi d'une réponse ---
if (isset($_POST['send_reply'])) {
    $message = htmlspecialchars($_POST['message_reply']);
    if (!empty($message)) {
        // On insère la réponse dans la BDD
        $insert_msg = $bdd->prepare(
            "INSERT INTO messages (id_exp, id_dest, objet, message) 
             VALUES (?, ?, ?, ?)"
        );
        // L'expéditeur est l'utilisateur actuel, le destinataire est celui de la conversation
        $insert_msg->execute([$user_id, $sender_id, "Re: ...", $message]); 
        
        // On rafraîchit la page pour voir le message envoyé
        header('Location: lecture?id=' . $sender_id);
        exit();
    }
}

// --- Action : Marquer les messages de cet expéditeur comme LUS ---
$update_read = $bdd->prepare(
    "UPDATE messages SET lu = 1 
     WHERE id_exp = ? AND id_dest = ?"
);
$update_read->execute([$sender_id, $user_id]);

// --- Récupération : Infos de l'expéditeur ---
$req_sender = $bdd->prepare("SELECT nom, prenom FROM users WHERE id_u = ?");
$req_sender->execute([$sender_id]);
$sender = $req_sender->fetch();
if (!$sender) {
    die("Erreur : Expéditeur inconnu.");
}
$sender_name = htmlspecialchars($sender['prenom'] . ' ' . $sender['nom']);

// --- Récupération : Toute la conversation ---
$req_messages = $bdd->prepare(
    "SELECT * FROM messages 
     WHERE (id_exp = :user_id AND id_dest = :sender_id) 
        OR (id_exp = :sender_id AND id_dest = :user_id)
     ORDER BY id ASC" // ASC pour un affichage chronologique
);
$req_messages->execute([
    'user_id' => $user_id,
    'sender_id' => $sender_id
]);
$messages = $req_messages->fetchAll();

?>

<div class="conversation-header mb-4">
    <a href="reception" class="btn btn-outline-warning me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="h3 text-white mb-0">Conversation avec <?= $sender_name ?></h1>
</div>

<div class="message-list">
    <?php foreach ($messages as $msg): ?>
        
        <?php if ($msg['id_exp'] == $user_id): ?>
            <div class="message-bubble sent">
                <div class="message-content"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <div class="message-meta">Envoyé</div>
            </div>
        <?php else: ?>
            <div class="message-bubble received">
                <div class="message-content"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <div class="message-meta">Reçu</div>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>

<div class="reply-form-container">
    <form method="POST" action="lecture?id=<?= $sender_id ?>" class="reply-form">
        <textarea name="message_reply" class="form-control" rows="3" placeholder="Écrivez votre réponse..." required></textarea>
        <button type="submit" name="send_reply" class="btn btn-warning ms-3">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>