<template>
  <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
          <div><i class="fas fa-table"></i> Danh sách Rạp</div>
          
          <div class="d-flex gap-2">
              <input type="text" class="form-control form-control-sm" v-model="searchQuery" @input="debouncedFetch" placeholder="Tìm tên, địa chỉ..." style="width: 200px;">
              
              <select class="form-select form-select-sm" v-model="filterStatus" @change="fetchCinemas" style="width: 150px;">
                  <option value="">Tất cả trạng thái</option>
                  <option value="ACTIVE">Đang hoạt động</option>
                  <option value="INACTIVE">Ngừng hoạt động</option>
                  <option value="TRASHED">Đã xóa (Ẩn)</option>
              </select>
          </div>
      </div>
      
      <div class="table-responsive">
          <table class="table table-hover mb-0">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Tên Rạp</th>
                      <th>Địa chỉ</th>
                      <th>Thành phố</th>
                      <th>Điện thoại</th>
                      <th>Trạng thái</th>
                      <th>Hành động</th>
                  </tr>
              </thead>
              <tbody>
                  <tr v-if="loading">
                      <td colspan="7" class="text-center py-4">
                          <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                          <p class="mt-2 text-muted">Đang tải dữ liệu...</p>
                      </td>
                  </tr>
                  <tr v-else-if="cinemas.length === 0">
                      <td colspan="7" class="text-center py-4 text-muted">
                          <i class="fas fa-inbox fa-2x mb-2"></i>
                          <p>Không tìm thấy rạp nào phù hợp.</p>
                      </td>
                  </tr>
                  <tr v-else v-for="cinema in cinemas" :key="cinema.id">
                      <td><strong>#{{ cinema.id }}</strong></td>
                      <td><strong>{{ cinema.name }}</strong></td>
                      <td>{{ cinema.address }}</td>
                      <td><span class="badge bg-info">{{ cinema.city }}</span></td>
                      <td>{{ cinema.phone || 'N/A' }}</td>
                      <td>
                          <span v-if="cinema.deleted_at" class="badge bg-secondary"><i class="fas fa-trash-alt"></i> Đã xóa</span>
                          <span v-else-if="cinema.status === 'ACTIVE'" class="badge bg-success"><i class="fas fa-check-circle"></i> Active</span>
                          <span v-else class="badge bg-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                      </td>
                      <td>
                          <div v-if="cinema.deleted_at">
                              <button class="btn btn-sm btn-success" @click="restoreCinema(cinema.id)" title="Khôi phục">
                                  <i class="fas fa-undo"></i> Khôi phục
                              </button>
                          </div>
                          <div v-else class="d-flex gap-1">
                              <a :href="`/admin/cinemas/${cinema.id}`" class="btn btn-sm btn-info" title="Xem">
                                  <i class="fas fa-eye"></i>
                              </a>
                              <a :href="`/admin/cinemas/${cinema.id}/edit`" class="btn btn-sm btn-warning" title="Sửa">
                                  <i class="fas fa-edit"></i>
                              </a>
                              <button class="btn btn-sm btn-danger" @click="deleteCinema(cinema.id)" title="Xóa mềm">
                                  <i class="fas fa-trash"></i>
                              </button>
                          </div>
                      </td>
                  </tr>
              </tbody>
          </table>
      </div>
      
      <div class="card-footer d-flex justify-content-between align-items-center" v-if="pagination.last_page > 1">
          <small class="text-muted">Hiển thị {{ pagination.from }} - {{ pagination.to }} trong {{ pagination.total }} rạp</small>
          <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                  <button class="page-link" @click="changePage(pagination.current_page - 1)">&laquo;</button>
              </li>
              <li class="page-item" v-for="page in pagination.last_page" :key="page" :class="{ active: pagination.current_page === page }">
                  <button class="page-link" @click="changePage(page)">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                  <button class="page-link" @click="changePage(pagination.current_page + 1)">&raquo;</button>
              </li>
          </ul>
      </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CinemaManager',
  data() {
      return {
          cinemas: [],
          loading: true,
          searchQuery: '',
          filterStatus: 'ACTIVE',
          pagination: {
              current_page: 1,
              last_page: 1,
              total: 0,
              from: 0,
              to: 0
          },
          debounceTimer: null
      };
  },
  mounted() {
      this.fetchCinemas();
  },
  methods: {
      async fetchCinemas(page = 1) {
          this.loading = true;
          try {
              const response = await axios.get('/admin/cinemas', {
                  params: {
                      page: page,
                      search: this.searchQuery,
                      status: this.filterStatus !== 'TRASHED' ? this.filterStatus : null,
                      trashed: this.filterStatus === 'TRASHED' ? 'true' : 'false'
                  },
                  headers: {
                      'Accept': 'application/json'
                  }
              });
              
              this.cinemas = response.data.data;
              this.pagination = {
                  current_page: response.data.current_page,
                  last_page: response.data.last_page,
                  total: response.data.total,
                  from: response.data.from,
                  to: response.data.to
              };
          } catch (error) {
              console.error('Lỗi khi tải danh sách rạp:', error);
          } finally {
              this.loading = false;
          }
      },
      debouncedFetch() {
          clearTimeout(this.debounceTimer);
          this.debounceTimer = setTimeout(() => {
              this.fetchCinemas(1);
          }, 300);
      },
      changePage(page) {
          if (page >= 1 && page <= this.pagination.last_page) {
              this.fetchCinemas(page);
          }
      },
      async deleteCinema(id) {
          if (!confirm('Bạn có chắc chắn muốn xóa rạp này không? Rạp sẽ được chuyển vào thùng rác.')) return;
          
          try {
              const response = await axios.delete(`/admin/cinemas/${id}`, {
                  headers: {
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                  }
              });
              
              if (response.data.success) {
                  alert(response.data.message);
                  this.fetchCinemas(this.pagination.current_page);
              }
          } catch (error) {
              alert(error.response?.data?.message || 'Có lỗi xảy ra!');
          }
      },
      async restoreCinema(id) {
          if (!confirm('Bạn có muốn khôi phục rạp này không?')) return;
          
          try {
              const response = await axios.post(`/admin/cinemas/${id}/restore`, {}, {
                  headers: {
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                  }
              });
              
              if (response.data.success) {
                  alert(response.data.message);
                  this.fetchCinemas(this.pagination.current_page);
              }
          } catch (error) {
              alert(error.response?.data?.message || 'Có lỗi xảy ra!');
          }
      }
  }
}
</script>
