<div class="dashboard-layout">

    <aside class="sidebar">

        <div class="logo">
            <h2>EditorVideoIA</h2>
            <span>Processamento em Massa</span>
        </div>

        <button class="menu-btn" id="btnUploadVideos">
            📥 Enviar vídeos
        </button>

        <button class="menu-btn">
            🖼 Templates
        </button>

        <button class="menu-btn">
            🎵 Músicas
        </button>

        <button class="menu-btn">
            ⚙ Configurações
        </button>

        <button class="menu-btn process">
            ▶ Processar vídeos
        </button>

    </aside>

    <main class="workspace">

        <div class="toolbar">

            <div class="toolbar-left">

                <label>
                    Carregar
                    <input
                        type="number"
                        id="dashboardLoadLimit"
                        value="100">
                </label>

                <label>
                    Tamanho
                    <select id="dashboardCardSize">
                        <option>Pequeno</option>
                        <option selected>Médio</option>
                        <option>Grande</option>
                    </select>
                </label>

                <label>
                    Colunas
                    <select id="dashboardColumns">
                        <option>4</option>
                        <option selected>5</option>
                        <option>6</option>
                        <option>7</option>
                    </select>
                </label>

            </div>

            <div class="toolbar-right">

                <button id="btnBatchCreate">
                    Criar fila
                </button>

                <button
                    id="btnBatchStart"
                    class="process">
                    ▶ PROCESSAR
                </button>

            </div>

        </div>

        <div
            id="batchList"
            class="video-grid">

        </div>

    </main>

</div>
