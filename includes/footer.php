        </div><!-- /.app-container -->
    </main><!-- /.main-content -->

    <!-- Global Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteModalTitle" style="color: var(--danger); display: flex; align-items: center; gap: 8px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Confirm Record Deletion
                </h3>
                <button type="button" class="alert-close close-modal-btn" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete the patient record for <strong id="deletePatientName" style="color: var(--text-main);">this patient</strong>?</p>
                <p style="margin-top: 8px; font-size: 0.85rem; color: var(--danger);">
                    ⚠️ This action cannot be undone. All clinical notes and admission details will be removed.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal-btn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Patient</button>
            </div>
        </div>
    </div>

    <!-- Application Footer -->
    <footer class="footer">
        <div class="app-container">
            <div class="footer-inner">
                <div>
                    <strong>Hospital Patient Management System (HPMS)</strong> &bull; IT22013 Web System Technologies
                </div>
                <div>
                    Powered by <strong>PHP & MySQL</strong> &bull; &copy; <?= date('Y'); ?> MediCare Hospital Systems
                </div>
            </div>
        </div>
    </footer>

    <!-- Core Javascript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
