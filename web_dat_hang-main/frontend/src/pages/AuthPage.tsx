import  { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import LoginForm from '../components/LoginForm';
import RegisterForm from '../components/RegisterForm';
import BackgroundEffects from '../components/BackgroundEffects';
import api from '../services/api';

export default function AuthPage() {
  const [isRegister, setIsRegister] = useState(false);
  const [error, setError] = useState('');
  
  // State hiển thị màn hình chờ khi đang SSO
  const [isSSOLoading, setIsSSOLoading] = useState(false);
  
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  /* ---- LOGIC SSO (TỰ ĐỘNG ĐĂNG NHẬP) ---- */
  useEffect(() => {
    const handleSSO = async () => {
      const email = searchParams.get('email');
      const signature = searchParams.get('signature');

      // Nếu phát hiện có tham số SSO từ Worksuite
      if (email && signature) {
        setIsSSOLoading(true); // Bật màn hình chờ
        try {
          console.log('Đang xử lý đăng nhập SSO từ Worksuite...');
          
          // Gọi API Backend xác thực chữ ký
          const { data } = await api.post('/sso-login', { email, signature });
          
          // Nếu thành công:
          localStorage.setItem('token', data.token);
          localStorage.setItem('user', JSON.stringify(data.user));
          
          // Kích hoạt quét đơn hàng ngầm (giống logic login thường)
          api.post('/admin/send-reminders')
             .then(() => console.log('Đã kích hoạt quét đơn hàng ngầm.'))
             .catch(err => console.error('Lỗi kích hoạt nhắc nhở:', err));

          // Chuyển hướng ngay lập tức
          navigate('/dashboard'); 

        } catch (err: any) {
          console.error('Lỗi SSO:', err);
          setError('Xác thực từ Worksuite thất bại. Vui lòng đăng nhập thủ công.');
          setIsSSOLoading(false); // Tắt màn hình chờ để hiện form login
        }
      }
    };

    handleSSO();
  }, [searchParams, navigate]);

  /* ---- LOGIN THƯỜNG ---- */
  const handleLogin = async (email: string, password: string) => {
    try {
      const { data } = await api.post('/login', { email, password });
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      api.post('/admin/send-reminders')
        .then(() => console.log('Đã kích hoạt quét đơn hàng ngầm.'))
        .catch(err => console.error('Lỗi kích hoạt nhắc nhở:', err));
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Đăng nhập thất bại');
    }
  };

  /* ---- REGISTER ---- */
  const handleRegister = async (formData: any) => {
    try {
      const { data } = await api.post('/register', formData);
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      navigate('/dashboard');
    } catch (err: any) {
      setError(err.response?.data?.error || 'Đăng ký thất bại');
    }
  };

  /* ---- UI ---- */
  return (
    <div className="min-h-screen relative overflow-hidden flex items-center justify-center">
      <BackgroundEffects />
      <div className="relative z-10 w-full max-w-md p-4">
        
        {/* HIỂN THỊ KHI ĐANG LOAD SSO */}
        {isSSOLoading ? (
          <div className="backdrop-blur-2xl bg-white/10 dark:bg-black/30 border border-white/20 rounded-3xl p-8 shadow-2xl text-center animate-pulse">
            <div className="mb-4">
               {/* Icon Loading đơn giản */}
               <svg className="animate-spin h-10 w-10 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h2 className="text-xl font-bold text-white mb-2">Đang kết nối Worksuite...</h2>
            <p className="text-gray-300 text-sm">Vui lòng đợi trong giây lát</p>
          </div>
        ) : (
          /* HIỂN THỊ FORM LOGIN/REGISTER BÌNH THƯỜNG */
          <div className="w-full max-w-md">
            <div className="backdrop-blur-2xl bg-white/10 dark:bg-black/30 border border-white/20 rounded-3xl p-8 shadow-[0_8px_32px_0_rgba(31,38,135,0.37)] animate-fade-in-up">
              {isRegister ? (
                <>
                  <RegisterForm onSubmit={handleRegister} />
                  <p className="text-sm text-gray-300 text-center mt-4">
                    Đã có tài khoản?{' '}
                    <button
                      onClick={() => {
                        setIsRegister(false);
                        setError('');
                      }}
                      className="text-indigo-400 hover:underline"
                    >
                      Đăng nhập
                    </button>
                  </p>
                </>
              ) : (
                <>
                  <LoginForm onSubmit={handleLogin}
                    onSwitchToRegister={() => {
                      setIsRegister(true);
                      setError('');
                    }}
                  />
                </>
              )}

              {error && (
                <div className="mt-4 text-red-200 bg-red-500/20 p-2 rounded-lg text-sm text-center border border-red-500/30">{error}</div>
              )}
            </div>
          </div>
        )}
      </div>
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-1/2 h-[1px] bg-gradient-to-r from-transparent via-white/80 to-transparent"></div>
    </div>
  );
}