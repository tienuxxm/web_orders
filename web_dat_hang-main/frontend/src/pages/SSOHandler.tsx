import { useEffect, useRef, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '../services/api';
import toast from 'react-hot-toast';
import { useTheme } from '../context/ThemeContext';
// Import icon để hiển thị lỗi cho đẹp (Nếu project chưa có lucide-react thì có thể dùng thẻ <svg> thường)
import { AlertCircle, ShieldAlert } from 'lucide-react'; 

const SSOHandler = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { setTheme } = useTheme();
  
  // State để lưu lỗi. Nếu null nghĩa là đang chạy, nếu có string là có lỗi
  const [error, setError] = useState<string | null>(null);
  
  const processingRef = useRef(false);

  useEffect(() => {
    if (processingRef.current) return;
    processingRef.current = true;

    const handleSSO = async () => {
      // 1. Xử lý Theme
      const themeParam = searchParams.get('theme');
      if (themeParam === 'dark' || themeParam === 'light') {
         setTheme(themeParam);
      }

      const email = searchParams.get('email');
      const timestamp = searchParams.get('timestamp');
      const signature = searchParams.get('signature');
      const isIframe = searchParams.get('is_iframe');
      const redirectPath = searchParams.get('redirect');

      if (!email || !signature) {
        setError('Đường dẫn truy cập thiếu thông tin xác thực.');
        return;
      }

      try {
        const res = await api.post('/auth/sso-verify', { email, timestamp, signature });
        const { token, user } = res.data;

        if (token) {
          localStorage.setItem('token', token);
          localStorage.setItem('user', JSON.stringify(user));
          
          if (isIframe === '1') {
            sessionStorage.setItem('IS_EMBEDDED', 'true');
          }

          toast.success('Kết nối thành công!');
          navigate(redirectPath || '/orders');
        }
      } catch (err: any) {
        console.error('Lỗi SSO:', err);
        
        if (err.response) {
            const status = err.response.status;
            if (status === 404) {
                setError('Tài khoản Bitex của bạn chưa có thông tin trên hệ thống Đặt hàng.');
            } else if (status === 401) {
                setError('Xác thực thất bại. Chữ ký bảo mật không khớp.');
            } else if (status === 403) {
                setError('Tài khoản của bạn không có quyền truy cập chức năng này.');
            } else {
                setError(err.response.data?.message || 'Có lỗi xảy ra khi kết nối máy chủ.');
            }
        } else {
            setError('Không thể kết nối đến máy chủ. Vui lòng kiểm tra mạng.');
        }
      }
    };

    handleSSO();
  }, [searchParams, navigate, setTheme]);

  // --- GIAO DIỆN HIỂN THỊ ---

  // 1. Trường hợp CÓ LỖI -> Hiển thị thông báo "Không có quyền"
  if (error) {
    return (
      <div className="flex flex-col items-center justify-center h-screen bg-gray-50 dark:bg-bitex-dark px-4">
        <div className="max-w-md w-full bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8 text-center border border-gray-200 dark:border-gray-700">
            <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 dark:bg-red-900 mb-6">
                <svg className="h-8 w-8 text-red-600 dark:text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Truy cập bị từ chối
            </h2>
            
            <p className="text-gray-600 dark:text-gray-300 mb-6">
                {error}
            </p>
            
            <div className="p-4 bg-blue-50 dark:bg-gray-700 rounded-lg text-sm text-blue-700 dark:text-blue-200">
                Vui lòng liên hệ Admin hoặc bộ phận IT nếu bạn cho rằng đây là một sự nhầm lẫn.
            </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-center justify-center h-screen bg-gray-50 dark:bg-bitex-dark">
      <div className="text-center">
        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p className="text-gray-600 dark:text-white font-medium text-lg">Đang xác thực thông tin...</p>
      </div>
    </div>
  );
};

export default SSOHandler;