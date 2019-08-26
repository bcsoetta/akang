<div>
    <h2>List Gudang Terdaftar</h2>
    <hr>
    <table class="table" id="tblGudang">
        <thead>
            <th>No</th>
            <th>Kode</th>
            <th>Grup</th>
            <th></th>
        </thead>
        <tbody>
            <!-- <tr>
                <td>1</td>
                <td>GDHL</td>
                <td>LINI1</td>
                <td>
                <a href="<?php echo base_url('gudang/delete/GDHL');?>" class="commonButton inblock shAnim redGrad" onclick="return confirm('Delete gudang : GDHL?')">Delete</a>
                </td>
            </tr> -->
            <?php 
            $nomor = 1;

            foreach ($listGudang as $gudang) {
            ?>

            <tr>
                <td> <?php echo $nomor++;?> </td>
                <td> <?php echo $gudang['gudang'];?> </td>
                <td> <?php echo $gudang['grup'];?> </td>
                <td>
                <a href="<?php echo base_url('gudang/delete/' . $gudang['gudang']);?>" class="commonButton inblock shAnim redGrad" onclick="return confirm('Delete gudang : <?php echo $gudang['gudang'];?>?')">Delete</a>
                </td>
            </tr>

            <?php 
            }
            ?>
        </tbody>
    </table>
    <hr>
    <form method="POST">
        <div>
            <label for="kdGudang">Tambah Gudang</label>
            <input type="text" id="kdGudang" class="shAnim shInput" name="kode_gudang" placeholder="kode gudang..." required/>
            <input type="text" id="grpGudang" class="shAnim shInput" name="grup_gudang" placeholder="grup gudang..." required/>
            <input type="submit" class="commonButton shAnim inblock blueGrad" name="action" value="Tambah" />
        </div>
    </form>
</div>

