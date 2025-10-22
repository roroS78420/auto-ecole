<?php     
auth(1); // Authentification de l'élève
global $bdd;

// --- 1. LOGIQUE PHP CORRIGÉE (ON CRÉE UN VRAI TABLEAU) ---
$requete = $bdd->prepare(
            "SELECT l.titre, l.date_l, l.date_fin, l.description, m.prenom, m.nom     
                 FROM lessons l
                 JOIN users m ON l.id_m = m.id_u
                 WHERE l.id_e = ?"
);
$requete->execute([$_SESSION['id_u']]);
$lessons = $requete->fetchAll();

$events = []; // On crée un tableau PHP vide
foreach($lessons as $lesson) {
            // On ajoute un sous-tableau pour chaque événement
            $events[] = [
                        'title' => $lesson['titre'],
                        'start' => $lesson['date_l'],
                        'end'           => $lesson['date_fin'],
                        'extendedProps' => [ // Données supplémentaires pour le double-clic
                                    'description'      => $lesson['description'],
                                    'moniteur_name' => $lesson['prenom'] . ' ' . $lesson['nom']
                        ]
            ];
}
?>

<div class="d-flex justify-content-center pt-4">
            <h3 class="text-center text-white">Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?> ! Voici votre planning de la semaine.</h3>
</div>

<div class="calendar-container">
            <div id="calendar"></div>
</div>
            <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.13/main.min.css' rel='stylesheet' />
            <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
                        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>           

<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true" data-bs-theme="dark">
            <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg">

                                                                        <div class="modal-header border-0">
                                                <h4 id="view_titre" class="modal-title text-warning w-100"></h4>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body pt-0">
                                                                                                <h6 class="text-white-50 mb-3" id="eventDetailsModalLabel">Détails de votre cours</h6>

                                                                                                <ul class="list-unstyled">
                                                            <li class="mb-3 d-flex align-items-center">
                                                                        <i class="bi bi-person-check-fill fs-5 text-warning me-3"></i>
                                                                        <div>
                                                                                    <small class="text-white-50">Moniteur</small>
                                                                                    <div id="view_moniteur" class="text-light"></div>
                                                                        </div>
                                                            </li>
                                                                
                                                            <li class="mb-3 d-flex align-items-center">
                                                                        <i class="bi bi-calendar-event fs-5 text-warning me-3"></i>
                                                                        <div>
                                                                                    <small class="text-white-50">Début</small>
                                                                                    <div id="view_start" class="text-light"></div>
                                                                        </div>
                                                            </li>
                                                                
                                                            <li class="mb-3 d-flex align-items-center">
                                                                        <i class="bi bi-calendar-event-fill fs-5 text-warning me-3"></i>
                                                                        <div>
                                                                                    <small class="text-white-50">Fin</small>
                                                                                    <div id="view_end" class="text-light"></div>
                                                                        </div>
                                                            </li>
                                                </ul>

                                                                                                <hr class="border-warning-subtle">
                                                <p class="mb-1 text-white-50">Description :</p>
                                                <p id="view_description" class="text-light" style="white-space: pre-wrap;"></p>
                                    </div>

                                    <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                        </div>
            </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                            
                        initialView: 'timeGridWeek',
                        height: 'auto',
                        locale: 'fr',
                        slotMinTime: '08:00:00',
                        slotMaxTime: '20:00:00',

                        headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                            
                        nowIndicator: true,
                        navLinks: true,
                        editable: false, // L'élève ne peut pas modifier

                        // On utilise json_encode pour passer le VRAI tableau
                        events: <?php echo json_encode($events); ?>,

// --- NOUVELLE GESTION DU DOUBLE-CLIC (v6) ---
                        eventDidMount: function(info) {
                                    // "info.el" est l'élément HTML du cours
                                    // On lui attache un écouteur de double-clic
                                    info.el.addEventListener('dblclick', function() {
                                                    
                                                let event = info.event;
                                                let props = event.extendedProps;
                                                    
                                                // On récupère la modale (qui existe déjà dans le HTML)
                                                let modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
                                                    
                                                // Formater les dates pour un affichage lisible
                                                let startDate = event.start.toLocaleString('fr-FR', { dateStyle: 'long', timeStyle: 'short' });
                                                let endDate = event.end ? event.end.toLocaleString('fr-FR', { dateStyle: 'long', timeStyle: 'short' }) : 'N/A';

                                                // On remplit les champs de la modale
                                                document.getElementById('view_titre').textContent = event.title;
                                                document.getElementById('view_moniteur').textContent = props.moniteur_name;
                                                document.getElementById('view_start').textContent = startDate;
                                                document.getElementById('view_end').textContent = endDate;
                                                document.getElementById('view_description').textContent = props.description || "Aucune description fournie.";
                                                    
                                                // On affiche la modale
                                                modal.show();
                                    });
                        }
                        });
                        calendar.render();
});
</script>