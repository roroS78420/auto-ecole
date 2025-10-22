<?php 
auth(2); // Authentification du moniteur
global $bdd; // Assure-toi que $bdd est disponible

// --- GESTION DE L'AJOUT D'UN COURS ---
if (isset($_POST['submit_add'])) {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $date_l = $_POST['date_l'];
    $date_fin = $_POST['date_fin'];
    $id_e = (int)$_POST['id_e'];
    $id_m = (int)$_POST['id_m'];
    
    if (!empty($titre) && !empty($date_l) && !empty($date_fin) && $id_e > 0 && $id_m > 0) {
        $insert = $bdd->prepare("INSERT INTO lessons (titre, description, date_l, date_fin, id_e, id_m) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$titre, $description, $date_l, $date_fin, $id_e, $id_m]);
        header('Location: agenda-admin'); 
        exit();
    }
}

// --- GESTION DE L'ÉDITION D'UN COURS ---
if (isset($_POST['submit_edit'])) {
    $id_l = (int)$_POST['id_l'];
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    $date_l = $_POST['date_l'];
    $date_fin = $_POST['date_fin'];
    $id_e = (int)$_POST['id_e'];
    
    // Sécurité: le moniteur ne peut modifier que ses propres cours
    $stmt = $bdd->prepare(
        "UPDATE lessons SET 
            titre = ?, description = ?, date_l = ?, date_fin = ?, id_e = ?
         WHERE id_l = ? AND id_m = ?"
    );
    $stmt->execute([$titre, $description, $date_l, $date_fin, $id_e, $id_l, $_SESSION['id_u']]);
    
    header('Location: agenda-admin'); 
    exit();
}

// --- GESTION DE LA SUPPRESSION D'UN COURS ---
if (isset($_POST['submit_delete'])) {
    $id_l = (int)$_POST['id_l'];
    
    // Sécurité: le moniteur ne peut supprimer que ses propres cours
    $stmt = $bdd->prepare("DELETE FROM lessons WHERE id_l = ? AND id_m = ?");
    $stmt->execute([$id_l, $_SESSION['id_u']]);
    
    header('Location: agenda-admin'); 
    exit();
}

// --- GESTION DU DÉPLACEMENT (DRAG-AND-DROP) ---
if (isset($_GET['action']) && $_GET['action'] == 'move') {
    $id_l = (int)$_GET['id'];
    $start = $_GET['start'];
    $end = $_GET['end'];
    $moniteur_id = (int)$_SESSION['id_u'];

    // On vérifie que les données sont valides avant de mettre à jour
    if ($id_l > 0 && !empty($start) && !empty($end)) {
        $stmt = $bdd->prepare(
            "UPDATE lessons 
             SET date_l = ?, date_fin = ? 
             WHERE id_l = ? AND id_m = ?" // Sécurité : on vérifie que le cours lui appartient
        );
        $stmt->execute([$start, $end, $id_l, $moniteur_id]);
    }
    
    // On recharge la page pour que l'utilisateur n'ait pas l'URL "move" dans sa barre
    header('Location: agenda-admin');
    exit();
}

// --- RÉCUPÉRATION DES ÉVÉNEMENTS (AVEC TOUTES LES INFOS) ---
// On récupère tous les cours (même ceux des autres moniteurs, pour l'affichage)
$requete = $bdd->prepare(
    "SELECT l.*, e.prenom as eleve_prenom, e.nom as eleve_nom 
     FROM lessons l
     LEFT JOIN users e ON l.id_e = e.id_u"
);
$requete->execute();
$lessons = $requete->fetchAll();

$events = [];
foreach($lessons as $lesson) {
    // On ajoute une couleur différente si ce n'est pas le cours du moniteur connecté
    $color = ($lesson['id_m'] == $_SESSION['id_u']) ? 'var(--accent-color)' : '#555';
    $textColor = ($lesson['id_m'] == $_SESSION['id_u']) ? '#000' : '#fff';

    $events[] = [
        'id'    => $lesson['id_l'],
        'title' => $lesson['titre'],
        'start' => $lesson['date_l'],
        'end'   => $lesson['date_fin'],
        'color' => $color,
        'textColor' => $textColor,
        'extendedProps' => [ // Données supplémentaires pour le JS
            'description' => $lesson['description'],
            'id_m'        => (int)$lesson['id_m'],
            'id_e'        => (int)$lesson['id_e'],
            'eleve_name'  => $lesson['eleve_prenom'] . ' ' . $lesson['eleve_nom']
        ]
    ];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white"><i class="bi bi-calendar-week-fill me-3"></i>Planning des Moniteurs</h1>
    <button type="button" class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle me-2"></i>Ajouter un cours
    </button>
</div>

<div class="calendar-container">
    <div id="calendar"></div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-dark-theme">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Ajouter un nouveau cours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="agenda-admin">
                    
                    <div class="mb-3">
                        <label for="add_titre" class="form-label">Titre du cours</label>
                        <input type="text" name="titre" id="add_titre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description (optionnel)</label>
                        <textarea name="description" id="add_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_date_l" class="form-label">Début</label>
                            <input type="datetime-local" name="date_l" id="add_date_l" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_date_fin" class="form-label">Fin</label>
                            <input type="datetime-local" name="date_fin" id="add_date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_id_e" class="form-label">Élève</label>
                        <select name="id_e" id="add_id_e" class="form-select" required>
                            <option value="" selected disabled>Choisir un élève...</option>
                            <?php 
                            $requete = $bdd->query("SELECT * FROM users WHERE lvl = 1 ORDER BY prenom, nom");
                            $lesEleves = $requete->fetchAll();
                            foreach ($lesEleves as $unEleve) { ?>
                                <option value="<?= $unEleve['id_u'] ?>"><?= htmlspecialchars($unEleve['prenom'] . ' ' . $unEleve['nom']) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_id_m" class="form-label">Moniteur</label>
                        <select name="id_m" id="add_id_m" class="form-select" required>
                            <option value="" selected disabled>Choisir un moniteur...</option>
                            <?php 
                            $requete = $bdd->query("SELECT * FROM users WHERE lvl = 2 ORDER BY prenom, nom");
                            $lesMoniteurs = $requete->fetchAll();
                            foreach ($lesMoniteurs as $unMoniteur) { ?>
                                <option value="<?= $unMoniteur['id_u'] ?>" <?= ($unMoniteur['id_u'] == $_SESSION['id_u']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($unMoniteur['prenom'] . ' ' . $unMoniteur['nom']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="submit_add" class="btn btn-warning fw-bold">
                            Ajouter ce cours
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-dark-theme">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Détails du cours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="eventDetailsForm" method="post" action="agenda-admin">
                    <input type="hidden" name="id_l" id="edit_id_l">
                    
                    <div class="mb-3">
                        <label for="edit_titre" class="form-label">Titre du cours</label>
                        <input type="text" name="titre" id="edit_titre" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_date_l" class="form-label">Début</label>
                            <input type="datetime-local" name="date_l" id="edit_date_l" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_date_fin" class="form-label">Fin</label>
                            <input type="datetime-local" name="date_fin" id="edit_date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_id_e" class="form-label">Élève</label>
                        <select name="id_e" id="edit_id_e" class="form-select" required>
                            <?php 
                            // On ré-exécute la requête pour avoir les élèves dans la modale
                            $requete_eleves = $bdd->query("SELECT * FROM users WHERE lvl = 1 ORDER BY prenom, nom");
                            $lesEleves = $requete_eleves->fetchAll();
                            foreach ($lesEleves as $unEleve) { ?>
                                <option value="<?= $unEleve['id_u'] ?>"><?= htmlspecialchars($unEleve['prenom'] . ' ' . $unEleve['nom']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <input type="hidden" name="id_m" id="edit_id_m">

                </form>
            </div>
            <div class="modal-footer" id="modalFooter">
                </div>
        </div>
    </div>
</div>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    // Fonction de formatage pour les champs datetime-local
    function formatISOLocal(d) {
        if (!d) return '';
        let dateObj = new Date(d);
        if (isNaN(dateObj.getTime())) return ''; // Vérifie si la date est valide
        
        let year = dateObj.getFullYear();
        let month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
        let day = dateObj.getDate().toString().padStart(2, '0');
        let hours = dateObj.getHours().toString().padStart(2, '0');
        let minutes = dateObj.getMinutes().toString().padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        
        initialView: 'timeGridWeek',
        locale: 'fr',
        height: 'auto',
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        nowIndicator: true,
        navLinks: true,
        editable: true,
        selectable: true,
        selectMirror: true,
        events: <?php echo json_encode($events); ?>, 

        // --- GESTION DU DRAG-AND-DROP (DÉPLACEMENT) ---
        eventDrop: function(info) {
            // Sécurité : on vérifie que c'est bien le cours du moniteur connecté
            if (info.event.extendedProps.id_m !== <?php echo $_SESSION['id_u']; ?>) {
                alert("Vous ne pouvez pas déplacer le cours d'un autre moniteur.");
                info.revert();
                return;
            }

            if (!confirm("Voulez-vous déplacer ce cours ?")) {
                info.revert(); // Annule si l'utilisateur dit non
                return;
            }
            
            let eventId = info.event.id;
            let newStart = formatISOLocal(info.event.start);
            let newEnd = info.event.end ? formatISOLocal(info.event.end) : newStart;

            // On recharge la page avec les infos dans l'URL
            window.location.href = `agenda-admin?action=move&id=${eventId}&start=${newStart}&end=${newEnd}`;
        },

        // --- GESTION DU CLIC POUR CRÉER (SÉLECTION) ---
        select: function(arg) {
            var addModal = new bootstrap.Modal(document.getElementById('addModal'));
            // Pré-remplir les dates
            document.querySelector('#addModal input[name="date_l"]').value = formatISOLocal(arg.start);
            document.querySelector('#addModal input[name="date_fin"]').value = formatISOLocal(arg.end);
            addModal.show();
            calendar.unselect();
        },

        // --- GESTION DU DOUBLE-CLIC (MODIFIÉ) ---
        eventDidMount: function(info) {
            info.el.addEventListener('dblclick', function() {
                
                let event = info.event;
                let props = event.extendedProps;
                let currentUserId = <?php echo $_SESSION['id_u']; ?>;
                
                let modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
                let modalTitle = document.getElementById('eventDetailsModalLabel');
                let modalFooter = document.getElementById('modalFooter');
                let form = document.getElementById('eventDetailsForm');
                
                // Remplir le formulaire avec les données de l'événement
                document.getElementById('edit_id_l').value = event.id;
                document.getElementById('edit_titre').value = event.title;
                document.getElementById('edit_description').value = props.description || '';
                document.getElementById('edit_date_l').value = formatISOLocal(event.start);
                document.getElementById('edit_date_fin').value = event.end ? formatISOLocal(event.end) : formatISOLocal(event.start);
                document.getElementById('edit_id_e').value = props.id_e;
                document.getElementById('edit_id_m').value = props.id_m;
                
                // Vider le footer avant de mettre les bons boutons
                modalFooter.innerHTML = '';

                // CAS 1: C'est le cours du moniteur connecté
                if (props.id_m === currentUserId) {
                    modalTitle.textContent = "Modifier le cours";
                    form.style.pointerEvents = 'auto'; // Formulaire éditable

                    // Bouton Enregistrer
                    let saveButton = document.createElement('button');
                    saveButton.type = 'submit';
                    saveButton.name = 'submit_edit';
                    saveButton.className = 'btn btn-warning fw-bold';
                    saveButton.textContent = 'Enregistrer';
                    saveButton.form = 'eventDetailsForm'; // Lie le bouton au formulaire
                    
                    // Bouton Supprimer
                    let deleteButton = document.createElement('button');
                    deleteButton.type = 'submit';
                    deleteButton.name = 'submit_delete';
                    deleteButton.className = 'btn btn-outline-danger me-auto';
                    deleteButton.textContent = 'Supprimer';
                    deleteButton.form = 'eventDetailsForm'; // Lie le bouton au formulaire
                    deleteButton.onclick = () => confirm('Êtes-vous sûr de vouloir supprimer ce cours ?');

                    modalFooter.appendChild(deleteButton);
                    modalFooter.appendChild(saveButton);

                } 
                // CAS 2: C'est le cours d'un autre moniteur
                else {
                    modalTitle.textContent = "Détails (Lecture seule)";
                    form.style.pointerEvents = 'none'; // Formulaire bloqué (lecture seule)

                    // Bouton Fermer
                    let closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.className = 'btn btn-secondary';
                    closeButton.textContent = 'Fermer';
                    closeButton.setAttribute('data-bs-dismiss', 'modal');
                    
                    modalFooter.appendChild(closeButton);
                }
                
                modal.show();
            });
        }
    });

    calendar.render();
});
</script>