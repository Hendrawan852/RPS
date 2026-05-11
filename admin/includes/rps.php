<div class="card premium-card">
    <div class="card-header-accent" style="height: 4px; background: linear-gradient(135deg, #1f2937 0%, #4b5563 100%); position: absolute; top: 0; left: 0; width: 100%;"></div>
    <div class="card-title" style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <div class="icon-box" style="background: rgba(31, 41, 55, 0.1); color: #1f2937; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fas fa-file-signature"></i>
        </div>
        <div>
            <span class="gradient-text" style="font-size: 22px; font-weight: 800; display: block;">Buat RPS Baru</span>
            <span style="font-size: 13px; color: var(--text-muted); font-weight: 400;">Lengkapi formulir di bawah untuk menyusun rencana pembelajaran</span>
        </div>
    </div>

    <form action="" method="POST" style="padding-top: 10px;">
        <!-- Section 1: Identitas Mata Kuliah -->
        <div class="form-section-header" style="margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; color: #64748b; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="fas fa-info-circle" style="margin-right: 5px;"></i> Identitas Mata Kuliah
        </div>

        <div class="form-group">
            <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-book-open" style="width: 20px; color: #1f2937;"></i> Judul Mata Kuliah</label>
            <input type="text" name="judul_mk" class="form-control" placeholder="Contoh: Pemrograman Berorientasi Objek" style="padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; transition: all 0.3s;" required>
        </div>

        <div class="form-row-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div class="form-group">
                <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-barcode" style="width: 20px; color: #1f2937;"></i> Kode MK</label>
                <input type="text" name="kode_mk" class="form-control" placeholder="TI101" style="padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0;" required>
            </div>
            <div class="form-group">
                <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-weight-hanging" style="width: 20px; color: #1f2937;"></i> Bobot (SKS)</label>
                <input type="number" name="bobot_sks" class="form-control" placeholder="3" style="padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0;" required>
            </div>
            <div class="form-group">
                <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-layer-group" style="width: 20px; color: #1f2937;"></i> Semester</label>
                <select name="semester" class="form-control" style="padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; appearance: none; background: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 15px center/12px;" required>
                    <option value="" disabled selected>Pilih Semester</option>
                    <option value="1">Semester 1 (Gasal)</option>
                    <option value="2">Semester 2 (Genap)</option>
                    <option value="3">Semester 3 (Gasal)</option>
                    <option value="4">Semester 4 (Genap)</option>
                    <option value="5">Semester 5 (Gasal)</option>
                    <option value="6">Semester 6 (Genap)</option>
                    <option value="7">Semester 7 (Gasal)</option>
                    <option value="8">Semester 8 (Genap)</option>
                </select>
            </div>
        </div>

        <!-- Section 2: Deskripsi & Materi -->
        <div class="form-section-header" style="margin-top: 35px; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; color: #64748b; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="fas fa-align-left" style="margin-right: 5px;"></i> Detail Konten Perkuliahan
        </div>

        <div class="form-group">
            <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-quote-left" style="width: 20px; color: #1f2937;"></i> Deskripsi Singkat MK</label>
            <textarea name="deskripsi_mk" class="form-control" rows="3" placeholder="Jelaskan deskripsi singkat mengenai tujuan dan cakupan mata kuliah ini..." style="padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; resize: vertical;"></textarea>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="font-weight: 600; color: #334155; margin-bottom: 8px; display: block;"><i class="fas fa-list-ul" style="width: 20px; color: #1f2937;"></i> Materi Pembelajaran / Pokok Pembahasan</label>
            <textarea name="materi_pembelajaran" class="form-control" rows="4" placeholder="Daftar materi atau pokok pembahasan utama... (Pisahkan dengan baris baru)" style="padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; resize: vertical;"></textarea>
        </div>

        <div class="form-actions" style="margin-top: 40px; display: flex; gap: 15px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
            <button type="reset" class="btn btn-secondary" style="background: #f1f5f9; color: #64748b; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; transition: all 0.3s; flex: 1;">
                <i class="fas fa-undo"></i> RESET
            </button>
            <button type="submit" name="simpan_rps" class="btn btn-primary" style="background: linear-gradient(135deg, #1f2937 0%, #4b5563 100%); color: white; border: none; padding: 12px 35px; border-radius: 10px; font-weight: 700; box-shadow: 0 4px 15px rgba(31, 41, 55, 0.2); transition: all 0.3s; flex: 1;">
                <i class="fas fa-save"></i> SIMPAN RPS
            </button>
        </div>
    </form>
</div>
