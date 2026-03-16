export interface User {
  id: number;
  name: string;
  email: string;
}

export interface BingoBoard {
  id: number;
  share_id: string;
  user_id: number;
  title: string;
  bingo_count: number;
  created_at: string;
  updated_at: string;
  items?: BingoItem[];
}

export interface BingoItem {
  id: number;
  bingo_board_id: number;
  label: string;
  is_achieved: boolean;
  achieved_at: string | null;
  position: number;
}

export interface BingoTemplate {
  name: string;
  title: string;
  items: string[];
}
