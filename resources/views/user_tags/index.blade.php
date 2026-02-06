@extends('layouts.main_template')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="main-card mb-3 card">
                <div class="card-header">
                    จัดการ Tags สำหรับพนักงาน
                    <div class="btn-actions-pane-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
                            <i class="fas fa-plus"></i> เพิ่ม Tag ใหม่
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success fade show" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger fade show" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">#</th>
                                    <th>ชื่อ Tag</th>
                                    <th class="text-center">สี</th>
                                    <th class="text-center">สถานะ</th>
                                    <th class="text-center">จำนวนผู้ใช้งาน</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tags as $index => $tag)
                                    <tr>
                                        <td class="text-center text-muted">#{{ $index + 1 }}</td>
                                        <td>
                                            <div class="widget-content p-0">
                                                <div class="widget-content-wrapper">
                                                    <div class="widget-content-left flex2">
                                                        <div class="widget-heading">{{ $tag->name }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $tag->color }} badge-dot p-2"
                                                title="{{ $tag->color }}"></span>
                                            <small class="text-muted ms-1">{{ $tag->color }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if ($tag->is_active)
                                                <div class="badge bg-success">ใช้งาน</div>
                                            @else
                                                <div class="badge bg-secondary">ปิดใช้งาน</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill">{{ $tag->users_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary btn-sm"
                                                onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}', {{ $tag->is_active }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('user-tags.destroy', $tag->id) }}" method="POST"
                                                class="d-inline-block"
                                                onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบ Tag นี้?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete(this.form)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted p-4">ไม่พบข้อมูล Tags</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">สร้าง Tag ใหม่</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user-tags.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="position-relative form-group">
                            <label for="name" class="">ชื่อ Tag</label>
                            <input name="name" id="name" placeholder="ชื่อ Tag..." type="text"
                                class="form-control" required>
                        </div>
                        <div class="position-relative form-group">
                            <label for="color" class="">สี (Bootstrap Class / Hex Code)</label>
                            <select name="color" id="color" class="form-select">
                                <option value="primary" class="text-primary">Primary (น้ำเงิน)</option>
                                <option value="secondary" class="text-secondary" selected>Secondary (เทา)</option>
                                <option value="success" class="text-success">Success (เขียว)</option>
                                <option value="danger" class="text-danger">Danger (แดง)</option>
                                <option value="warning" class="text-warning">Warning (เหลือง)</option>
                                <option value="info" class="text-info">Info (ฟ้า)</option>
                                <option value="dark" class="text-dark">Dark (ดำ)</option>
                            </select>
                            <small class="form-text text-muted">เลือกสีแสดงผลสำหรับ Tag นี้</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editTagModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไข Tag</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="position-relative form-group">
                            <label for="edit_name" class="">ชื่อ Tag</label>
                            <input name="name" id="edit_name" type="text" class="form-control" required>
                        </div>
                        <div class="position-relative form-group">
                            <label for="edit_color" class="">สี</label>
                            <select name="color" id="edit_color" class="form-select">
                                <option value="primary" class="text-primary">Primary (น้ำเงิน)</option>
                                <option value="secondary" class="text-secondary">Secondary (เทา)</option>
                                <option value="success" class="text-success">Success (เขียว)</option>
                                <option value="danger" class="text-danger">Danger (แดง)</option>
                                <option value="warning" class="text-warning">Warning (เหลือง)</option>
                                <option value="info" class="text-info">Info (ฟ้า)</option>
                                <option value="dark" class="text-dark">Dark (ดำ)</option>
                            </select>
                        </div>
                        <div class="position-relative form-check">
                            <input name="is_active" id="edit_is_active" type="checkbox" class="form-check-input"
                                value="1">
                            <label for="edit_is_active" class="form-check-label">ใช้งาน</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function editTag(id, name, color, isActive) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_color').value = color;
            document.getElementById('edit_is_active').checked = isActive;

            const form = document.getElementById('editForm');
            form.action = '/user-tags/' + id;

            const modal = new bootstrap.Modal(document.getElementById('editTagModal'));
            modal.show();
        }

        function confirmDelete(form) {
            if (confirm('ยืนยันการลบ Tag?')) {
                form.submit();
            }
        }
    </script>
@endsection
