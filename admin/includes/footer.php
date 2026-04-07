        </main> <!-- End Content -->
    </div> <!-- End Main Wrapper -->
    
    <script>
        function toggleSidebar(e) {
            if(e) e.preventDefault();
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
    </script>
</body>
</html>
