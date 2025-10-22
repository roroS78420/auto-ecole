<?php 
auth(1); 
$user_id = $_SESSION['id_u'];

// --- NOUVELLE LOGIQUE PHP ---
// On ne récupère que le DERNIER message de CHAQUE conversation (expéditeur)
// C'est beaucoup plus propre et plus performant.
$req_conversations = $bdd->prepare(
    "SELECT 
        m.id, m.objet, m.lu, m.id_exp, 
        u.nom, u.prenom 
     FROM messages m
     JOIN users u ON m.id_exp = u.id_u
     WHERE m.id IN (
        -- Cette sous-requête trouve l'ID du message le plus récent pour chaque expéditeur
        SELECT MAX(id) 
        FROM messages 
        WHERE id_dest = ? 
        GROUP BY id_exp
     )
     ORDER BY m.id DESC"
);
$req_conversations->execute([$user_id]);
$conversations = $req_conversations->fetchAll();
$msg_nbr = count($conversations);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-envelope-paper me-3"></i>Boîte de réception</h1>
    <a href="envoi" class="btn btn-warning fw-bold">Nouveau message</a>
</div>

<?php if ($msg_nbr == 0): ?>
    <div class="alert alert-secondary" role="alert" style="background-color: var(--sidebar-bg); border-color: var(--border-color); color: var(--text-secondary);">
        Vous n'avez aucun message pour le moment.
    </div>

<?php else: ?>
    <div class="list-group inbox-list">
        <?php foreach ($conversations as $convo): ?>
            <?php
                // On prépare le nom et les initiales de l'expéditeur
                $sender_name = htmlspecialchars($convo['prenom'] . ' ' . $convo['nom']);
                $sender_initials = strtoupper(substr($convo['prenom'], 0, 1) . substr($convo['nom'], 0, 1));
            ?>
            <a href="lecture?id=<?= $convo['id_exp'] ?>" class="list-group-item list-group-item-action <?= ($convo['lu'] == 0) ? 'unread' : '' ?>">
                <div class="d-flex w-100">
                    <div class="inbox-avatar"><?= $sender_initials ?></div>
                    
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <div class="sender-name"><?= $sender_name ?></div>
                            </div>
                        <div class="message-subject"><?= htmlspecialchars($convo['objet']) ?></div>
                    </div>

                    <?php if ($convo['lu'] == 0): ?>
                        <div class="unread-dot"></div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>